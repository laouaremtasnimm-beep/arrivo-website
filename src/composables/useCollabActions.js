/**
 * useCollabActions.js
 *
 * Handles all collab state transitions: fetch, accept, decline, counter, end.
 * Written to match collaborations.php exactly.
 *
 * API contract (from collaborations.php):
 *   GET  ?user_id=X                                    → list
 *   POST { initiator_id, partner_id, service_id,
 *          package_id, title, discount_pct, ... }      → create
 *   PUT  { id, action, actor_id, ...extras }           → accept|decline|counter
 *   DELETE { id, actor_id }                            → withdraw pending request
 *
 * PUT actions:
 *   accept  — partner accepts, or initiator accepts a counter
 *   decline — either party declines
 *   counter — provider sends counter { counter_discount_pct?,
 *              counter_start_date?, counter_end_date?, counter_message? }
 *
 * Injected dependencies (passed in by AgencyDashboard):
*   collaborations : Ref<{Array}>   — the local collab list
*   user           : ComputedRef    — from useAuth()
*   addOffer       : Function       — from useOffers()
*   deleteOffer    : Function       — from useOffers()
*   loadError      : Ref<{string}>  — dashboard error banner
 */

const API = '/arrivo-website/backend/api/v1'

export function useCollabActions({ collaborations, user, addOffer, deleteOffer, loadError }) {

  // ─── Helpers ──────────────────────────────────────────────────────────────

  /** Consistent user ID regardless of which key useAuth stored it under */
  function uid() {
    return Number(user.value?.userID ?? user.value?.id)
  }

  /** Patch a collab in the local list by its collabID */
  function patchLocal(collabID, patch) {
    const idx = collaborations.value.findIndex(c => c.collabID === collabID)
    if (idx !== -1) Object.assign(collaborations.value[idx], patch)
  }

  /**
   * Normalise a raw DB row (from collaborations.php) into the shape that
   * CollabRequestCard / CollabOfferCard / CollaborationsPanel expect.
   *
   * The DB returns flat joined columns like initiator_company, partner_company,
   * service_title, package_title — we reshape them here once.
   */
  function normalizeCollab(c) {
    const myId = uid()
    const isInitiator = c.initiator_id === myId

    return {
      // Identity
      collabID: c.id,
      direction: isInitiator ? 'outgoing' : 'incoming',
      status: c.status,
      isCounter: !!c.is_counter,
      offer_id: c.offer_id ?? null,
      package_id: c.package_id ?? null,
      service_id: c.service_id ?? null,

      // Offer terms
      title: c.title,
      discount: c.discount_pct,
      type: c.offer_type,
      startDate: c.start_date,
      endDate: c.end_date,
      description: c.message,

      // Timestamps
      sentDate: new Date(c.created_at).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric',
      }),

      // Counter terms (if status === 'countered')
      counterData: c.counter_data ?? null,

      // Initiator display info — prefer company_name over personal name
      initiator: {
        id: c.initiator_id,
        name: c.initiator_company || `${c.initiator_first_name} ${c.initiator_last_name}`.trim(),
        role: 'agency',
      },

      // Partner display info
      partner: {
        id: c.partner_id,
        name: c.partner_company || `${c.partner_first_name} ${c.partner_last_name}`.trim(),
        role: 'provider',
        color: '#2EC4B6', // default teal; can be made dynamic later
      },

      // Attached content — shown in collab cards
      packageTitle: c.package_title ?? null,
      packageImg: c.package_img_url ?? null,
      packagePrice: c.package_price ?? null,
      serviceTitle: c.service_title ?? null,
      serviceIcon: c.service_icon ?? null,
      servicePrice: c.service_price ?? null,
      serviceUnit: c.service_price_unit ?? null,
    }
  }

  // ─── Fetch ────────────────────────────────────────────────────────────────

  /**
   * Load all collabs for the current user from the DB.
   * Replaces the hardcoded demo seed in AgencyDashboard.
   * Call this in onMounted alongside fetchBookings / fetchPackages.
   */
  async function fetchCollaborations() {
    try {
      const res = await fetch(`${API}/collaborations.php?user_id=${uid()}`)
      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to load collaborations')
      collaborations.value = (data.collaborations ?? []).map(normalizeCollab)
    } catch (e) {
      loadError.value = `Could not load collaborations: ${e.message}`
    }
  }

  // ─── Accept ───────────────────────────────────────────────────────────────

  /**
   * Accept an incoming collab request (or accept a counter-proposal).
   *
   * Backend creates the special_offer atomically and returns the full
   * updated collaboration row. We push the new offer into useOffers so
   * it appears on the Offers panel and Deals page immediately.
   */
  async function handleAcceptCollab(collab) {
    // Optimistic UI
    patchLocal(collab.collabID, { status: 'accepted' })

    try {
      const res = await fetch(`${API}/collaborations.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: collab.collabID,
          action: 'accept',
          actor_id: uid(),
        }),
      })

      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to accept collaboration')

      // Replace local stub with the full server-confirmed collab
      const confirmed = normalizeCollab(data.collaboration)
      patchLocal(collab.collabID, confirmed)

      // Push the new offer into the global useOffers store.
      // Shape mirrors what useOffers._bootstrap() produces from the DB.
      const c = data.collaboration
      addOffer({
        offerID: c.offer_id,
        title: c.title,
        discount: c.discount_pct,
        discount_pct: c.discount_pct,
        type: c.offer_type ?? 'Bundle',
        startDate: c.start_date ?? null,
        endDate: c.end_date ?? null,
        description: c.message ?? null,
        source: 'collab',
        active: true,
        is_active: 1,
        // Both owner references so either dashboard can recognise the offer
        owner_id: c.initiator_id,
        agency_id: c.initiator_id,
        provider_id: c.partner_id,
        // Content attached to this collab offer
        package_id: c.package_id ?? null,
        service_id: c.service_id ?? null,
        // Display metadata
        partner: confirmed.partner,
        initiator: confirmed.initiator,
      })

    } catch (e) {
      // Roll back optimistic update
      patchLocal(collab.collabID, { status: collab.status })
      loadError.value = `Could not accept collaboration: ${e.message}`
    }
  }

  // ─── Decline ──────────────────────────────────────────────────────────────

  async function handleDeclineCollab(collab) {
    patchLocal(collab.collabID, { status: 'declined' })

    try {
      const res = await fetch(`${API}/collaborations.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: collab.collabID,
          action: 'decline',
          actor_id: uid(),
        }),
      })

      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to decline collaboration')

      patchLocal(collab.collabID, normalizeCollab(data.collaboration))

    } catch (e) {
      patchLocal(collab.collabID, { status: collab.status })
      loadError.value = `Could not decline collaboration: ${e.message}`
    }
  }

  // ─── Counter ──────────────────────────────────────────────────────────────

  /**
   * Send a counter-proposal.
   *
   * collaborations.php counter action accepts:
   *   counter_discount_pct?, counter_start_date?,
   *   counter_end_date?, counter_message?
   *
   * CollabCounterForm emits: { title, discount, startDate, endDate, description }
   * We map those to the backend's expected keys here.
   *
   * The backend does NOT create a new collab row for a counter — it marks the
   * existing one as 'countered' and stores counter_data as JSON. The initiator
   * then sees the counter in their incoming tab and can accept or decline it.
   */
  async function handleCounterCollab({ original, counter }) {
    patchLocal(original.collabID, { status: 'countered' })

    // Map CollabCounterForm output → collaborations.php counter field names
    const body = {
      id: original.collabID,
      action: 'counter',
      actor_id: uid(),
    }

    // Only include fields that actually changed — the backend requires
    // at least one counter field to be present
    if (counter.discount && counter.discount !== original.discount)
      body.counter_discount_pct = Number(counter.discount)
    if (counter.startDate && counter.startDate !== original.startDate)
      body.counter_start_date = counter.startDate
    if (counter.endDate && counter.endDate !== original.endDate)
      body.counter_end_date = counter.endDate
    if (counter.description)
      body.counter_message = counter.description

    // Guard: CollabCounterForm requires a message but may not change terms.
    // Ensure at least counter_message is always included.
    if (!body.counter_message && counter.description) {
      body.counter_message = counter.description
    }

    try {
      const res = await fetch(`${API}/collaborations.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body),
      })

      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to send counter proposal')

      // Replace local row with server-confirmed state (includes counter_data)
      patchLocal(original.collabID, normalizeCollab(data.collaboration))

    } catch (e) {
      patchLocal(original.collabID, { status: 'pending' })
      loadError.value = `Could not send counter proposal: ${e.message}`
    }
  }

  // ─── End (terminate a live collab) ────────────────────────────────────────

  /**
   * End a live collaboration.
   * The collab status → 'ended'. The linked special_offer is deactivated
   * via DELETE /offers.php. The offer is removed from the useOffers store
   * via the injected deleteOffer function.
   */
  async function handleEndCollab(collab) {
    if (!confirm(`End the "${collab.title}" collaboration? The joint offer will be deactivated.`)) return

    const previousStatus = collab.status
    patchLocal(collab.collabID, { status: 'ended' })

    try {
      // 1. Mark the collab as ended
      const res = await fetch(`${API}/collaborations.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: collab.collabID,
          action: 'decline',   // 'decline' on an accepted collab = end it
          actor_id: uid(),
        }),
      })

      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to end collaboration')

      // 2. Deactivate the linked offer in the DB
      if (collab.offer_id) {
        await fetch(`${API}/offers.php`, {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: collab.offer_id }),
        })
        // 3. Remove from the in-memory useOffers store
        deleteOffer(collab.offer_id)
      }

    } catch (e) {
      patchLocal(collab.collabID, { status: previousStatus })
      loadError.value = `Could not end collaboration: ${e.message}`
    }
  }

  // ─── Withdraw (initiator cancels a pending request) ───────────────────────

  /**
   * Withdraw a pending outgoing request before the partner has responded.
   * Maps to DELETE /collaborations.php.
   */
  async function handleWithdrawCollab(collab) {
    if (!confirm(`Withdraw the "${collab.title}" request?`)) return

    const previousStatus = collab.status
    patchLocal(collab.collabID, { status: 'declined' })

    try {
      const res = await fetch(`${API}/collaborations.php`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: collab.collabID,
          actor_id: uid(),
        }),
      })

      const data = await res.json()
      if (!res.ok) throw new Error(data.error || 'Failed to withdraw request')

      // Remove it from the local list entirely
      collaborations.value = collaborations.value.filter(c => c.collabID !== collab.collabID)

    } catch (e) {
      patchLocal(collab.collabID, { status: previousStatus })
      loadError.value = `Could not withdraw request: ${e.message}`
    }
  }

  return {
    fetchCollaborations,
    handleAcceptCollab,
    handleDeclineCollab,
    handleCounterCollab,
    handleEndCollab,
    handleWithdrawCollab,
  }
}