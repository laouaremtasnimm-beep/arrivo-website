export function calculateDays(start, end) {
  if (!start || !end) return null
  const d1 = new Date(start)
  const d2 = new Date(end)
  if (isNaN(d1) || isNaN(d2)) return null
  
  const diffTime = Math.abs(d2 - d1)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays + 1 // Include both start and end days
}
