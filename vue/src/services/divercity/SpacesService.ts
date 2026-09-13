import { apiClient } from '@/plugins/axios/api'
import type { Space, SpaceHighlight, SpaceHighlightSubmission} from '@/models/interfaces/divercity/Space'
import type { EventActivityType, InformationSource, Booking, BookingSubmission, SpaceAvailability, BookingStatus } from '@/models/interfaces/divercity/Booking'
import type { PublicBooking } from '@/models/interfaces/divercity/Booking'
import type { BlockedPeriod, BlockedPeriodSubmission } from '@/models/interfaces/divercity/BlockedPeriod'



export class SpacesService {
  static async getSpaces(): Promise<Space[]> {
    const data = (
      await apiClient.get('/api/spaces', { headers: { accept: 'application/ld+json' } })
    ).data
    return data['hydra:member'] as Space[]
  }

  static async getPublicBookings(): Promise<PublicBooking[]> {
    const data = (
      await apiClient.get('/api/divercity/bookings/public', {
        headers: { accept: 'application/ld+json' }
      })
    ).data
    return data['hydra:member'] as PublicBooking[]
  }

  static async getBooking(id: string): Promise<Booking> {
    return (
      await apiClient.get(`/api/bookings/${id}`, { headers: { accept: 'application/ld+json' } })
    ).data
  }


  static async patchSpace(id: string, values: Partial<Space>): Promise<Space> {
    return (await apiClient.patch(`/api/spaces/${id}`, values)).data
  }

  static async postHighlight(highlight: SpaceHighlightSubmission & { space: string }): Promise<SpaceHighlight> {
    return (await apiClient.post('/api/space_highlights', highlight)).data
  }

  static async patchHighlight(id: number, values: SpaceHighlightSubmission): Promise<SpaceHighlight> {
    return (await apiClient.patch(`/api/space_highlights/${id}`, values)).data
  }

  static async deleteHighlight(id: number): Promise<void> {
    await apiClient.delete(`/api/space_highlights/${id}`)
  }

  static async getEventActivityTypes(): Promise<EventActivityType[]> {
    const data = (
      await apiClient.get('/api/event_activity_types', { headers: { accept: 'application/ld+json' } })
    ).data
    return data['hydra:member'] as EventActivityType[]
  }

  static async getInformationSources(): Promise<InformationSource[]> {
    const data = (
      await apiClient.get('/api/information_sources', { headers: { accept: 'application/ld+json' } })
    ).data
    return data['hydra:member'] as InformationSource[]
  }

  static async postBooking(booking: BookingSubmission): Promise<Booking> {
    return (await apiClient.post('/api/bookings', booking)).data
  }

 static async patchBookingInformationSource(
    bookingId: string,
    informationSource: string | null,
    informationSourceOther?: string
  ): Promise<Booking> {
    return (
      await apiClient.patch(`/api/divercity/bookings/${bookingId}/information-source`, {
        informationSource,
        ...(informationSourceOther ? { informationSourceOther } : {})
      })
    ).data
  }

  static async getSpaceAvailability(
    spaceId: string,
    dateFrom: string,
    dateTo: string
  ): Promise<SpaceAvailability[]> {
    const data = (
      await apiClient.get(`/api/divercity/spaces/${spaceId}/availability`, {
        params: { date_from: dateFrom, date_to: dateTo },
        headers: { accept: 'application/ld+json' }
      })
    ).data
    return data['hydra:member'] as SpaceAvailability[]
  }

  static async getManagedBookings(): Promise<Booking[]> {
    const data = (
      await apiClient.get('/api/divercity/bookings/managed', {
        headers: { accept: 'application/ld+json' }
      })
    ).data
    return data['hydra:member'] as Booking[]
  }

  static async getStatuses(): Promise<BookingStatus[]> {
    const data = (
      await apiClient.get('/api/statuses', { headers: { accept: 'application/ld+json' } })
    ).data
    return data['hydra:member'] as BookingStatus[]
  }

  static async patchBookingDecision(
    bookingId: string,
    status: string,
    refusalReason?: string
  ): Promise<Booking> {
    return (
      await apiClient.patch(`/api/bookings/${bookingId}`, {
        status,
        ...(refusalReason ? { refusalReason } : {})
      })
    ).data
  }

  static async patchBookingCancellation(bookingId: string, cancellationReason?: string): Promise<Booking> {
    return (
      await apiClient.patch(`/api/divercity/bookings/${bookingId}/cancel`, {
        ...(cancellationReason ? { cancellationReason } : {})
      })
    ).data
  }

  static async getBlockedPeriods(spaceId: string): Promise<BlockedPeriod[]> {
    const data = (
      await apiClient.get('/api/blocked_periods', {
        params: { space: `/api/spaces/${spaceId}`, isUnblocked: false },
        headers: { accept: 'application/ld+json' }
      })
    ).data
    return data['hydra:member'] as BlockedPeriod[]
  }

  static async postBlockedPeriod(blockedPeriod: BlockedPeriodSubmission): Promise<BlockedPeriod> {
    return (await apiClient.post('/api/blocked_periods', blockedPeriod)).data
  }

  static async deleteBlockedPeriod(id: string): Promise<void> {
    await apiClient.delete(`/api/blocked_periods/${id}`)
  }

  static async unblockBlockedPeriod(id: string): Promise<BlockedPeriod> {
    return (await apiClient.patch(`/api/blocked_periods/${id}`, { isUnblocked: true })).data
  }

  static async getMyBookings(page = 1): Promise<PaginatedResult<Booking>> {
    const data = (
      await apiClient.get('/api/divercity/bookings/mine', {
        params: { page },
        headers: { accept: 'application/ld+json' }
      })
    ).data

    return {
      items: data['hydra:member'] as Booking[],
      totalItems: data['hydra:totalItems'] ?? (data['hydra:member'] as Booking[]).length,
      currentPage: page,
      itemsPerPage: 20
    }
  }

  static async patchBookingEdit(bookingId: string, values: Record<string, any>): Promise<Booking> {
    return (await apiClient.patch(`/api/divercity/bookings/${bookingId}/edit`, values)).data
  }

  static async patchBookingResources(bookingId: string, resourceIris: string[]): Promise<Booking> {
    return (
      await apiClient.patch(`/api/divercity/bookings/${bookingId}/resources`, {
        resources: resourceIris
      })
    ).data
  }
}

export interface PaginatedResult<T> {
  items: T[]
  totalItems: number
  currentPage: number
  itemsPerPage: number
}


