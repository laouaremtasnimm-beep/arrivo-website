import { useOffers } from '@/composables/useOffers'

const API = '/arrivo-website/backend/api/v1'

export function useCollabAccept() {
    const { addOffer } = useOffers()

    async function handleAcceptCollab(collab, collaborations, user, loadError) {

        // 1. Optimistic UI — flip status instantly
        if (collaborations?.value) {
            const idx = collaborations.value.findIndex(
                c => (c.collabID ?? c.id) === (collab.collabID ?? collab.id)
            )
            if (idx !== -1) collaborations.value[idx].status = 'accepted'
        }

        try {
            // 2. Call collaborations.php PUT with the shape it actually expects:
            //    { id, action, actor_id }
            //    The PHP handler will create the offer row itself via createOfferFromCollab()
            const res = await fetch(`${API}/collaborations.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: collab.collabID ?? collab.id,
                    action: 'accept',
                    actor_id: Number(user.value.userID ?? user.value.id),
                }),
            })

            const data = await res.json()
            if (!res.ok) throw new Error(data.error || 'Failed to accept collaboration')

            // 3. The PHP already saved the offer to the DB.
            //    Read the offer_id it returned and inject into the frontend store
            //    so SpecialOffers, DealsPage and SearchPage update with no reload.
            const serverCollab = data.collaboration ?? {}
            const offerId = serverCollab.offer_id ?? Date.now()

            // Resolve final terms — counter values take precedence if they exist
            const counter = serverCollab.counter_data ?? collab.counterData ?? {}
            const discount = counter.discount_pct ?? serverCollab.discount_pct ?? collab.discount
            const startDate = counter.start_date ?? serverCollab.start_date ?? collab.startDate ?? null
            const endDate = counter.end_date ?? serverCollab.end_date ?? collab.endDate ?? null
            const description = counter.message ?? serverCollab.message ?? collab.description ?? ''

            addOffer({
                offerID: offerId,
                // Ownership — both sides so OffersPanel filters correctly
                owner_id: Number(user.value.userID ?? user.value.id),
                agency_id: serverCollab.package_agency_id ?? collab.agencyId ?? collab.agency_id ?? null,
                provider_id: serverCollab.service_provider_id ?? collab.providerId ?? collab.provider_id ?? null,
                // Core fields
                title: serverCollab.title ?? collab.title,
                discount,
                discount_pct: discount,
                startDate,
                endDate,
                start_date: startDate,
                end_date: endDate,
                description,
                type: serverCollab.offer_type ?? collab.type ?? 'Bundle',
                source: 'collab',
                active: true,
                // Partner branding — "🤝 with PartnerName" on DealCard / SpecialOffers
                partnerName: collab.partner?.name ?? collab.partnerName
                    ?? serverCollab.partner_company ?? null,
                partnerColor: collab.partner?.color ?? collab.partnerColor ?? '#2EC4B6',
                // Item linkage
                package_id: serverCollab.package_id ?? collab.packageId ?? collab.package_id ?? null,
                service_id: serverCollab.service_id ?? collab.serviceId ?? collab.service_id ?? null,
            })

        } catch (e) {
            // Roll back the optimistic update so the card doesn't show "Accepted" on failure
            if (collaborations?.value) {
                const idx = collaborations.value.findIndex(
                    c => (c.collabID ?? c.id) === (collab.collabID ?? collab.id)
                )
                if (idx !== -1) collaborations.value[idx].status = collab.status ?? 'pending'
            }
            const msg = 'Could not accept collaboration: ' + e.message
            if (loadError) loadError.value = msg
            console.error('[handleAcceptCollab]', e)
        }
    }

    return { handleAcceptCollab }
}