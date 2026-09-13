export function getBookingStatusColor(code?: string): string {
  switch (code) {
    case 'ACCEPTEE':
      return 'main-green'
    case 'REFUSEE':
    case 'ANNULEE':
      return 'main-red'
    default:
      return 'main-yellow'
  }
}