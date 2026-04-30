async function handleAcceptCollab(collab) {
    // 1. Update local UI immediately for responsiveness
    const idx = collaborations.value.findIndex(c => c.collabID === collab.collabID)
    if (idx !== -1) collaborations.value[idx].status = 'accepted'

    // 2. Prepare the data for the Database
    const newOffer = {
        owner_id: user.value.userID || user.value.id, // Ensure this matches your user object
        title: collab.title,
        discount: collab.discount,
        startDate: collab.startDate || null,
        endDate: collab.endDate || null,
        description: collab.description,
        type: collab.type || 'Bundle',
        source: 'collab', // This matches your DB 'source' column default
        active: 1
    }

    try {
        // 3. Send to Backend
        const res = await fetch(`${API}/offers.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newOffer)
        })

        if (!res.ok) throw new Error('Failed to save collaboration to database')

        // 4. Update the composable state so the UI shows the new offer
        const data = await res.json()
        addOffer({ ...newOffer, offerID: data.offer_id })

    } catch (e) {
        loadError.value = "Collab accepted locally, but failed to save to DB: " + e.message
    }
}