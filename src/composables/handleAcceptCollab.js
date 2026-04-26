
function handleAcceptCollab(collab) {
    const idx = collaborations.value.findIndex(c => c.collabID === collab.collabID)
    if (idx !== -1) collaborations.value[idx].status = 'accepted'

    const outIdx = collaborations.value.findIndex(
        c => c.direction === 'outgoing' && c.title === collab.title && c.status === 'pending'
    )
    if (outIdx !== -1) collaborations.value[outIdx].status = 'accepted'

    // Create a joint offer for both agency AND provider roles
    addOffer({
        offerID: Date.now(),
        collabID: collab.collabID,
        source: 'collab',
        title: collab.title,
        discount: collab.discount,
        type: collab.type || 'Bundle',
        startDate: collab.startDate || '',
        endDate: collab.endDate || '',
        description: collab.description,
        partnerName: collab.initiator?.name || collab.partner?.name,
        partnerColor: collab.partner?.color || '#2EC4B6',
        active: true,
    })
}