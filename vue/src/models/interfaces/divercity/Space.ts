import type { Timestampable } from '@/models/interfaces/common/Timestampable'
import type { SymfonyRelation } from '@/models/interfaces/SymfonyRelation'
import type { BaseMediaObject } from '@/models/interfaces/object/MediaObject'
import type { FileObject } from '@/models/interfaces/object/FileObject'

export interface SpaceStatistic {
  id?: number
  label: string
  value: string
  position: number
}

export interface SpaceHighlight extends SymfonyRelation {
  id: number
  year: number
  semester: 1 | 2
  report?: FileObject
  statistics: SpaceStatistic[]
  createdAt: string
  updatedAt: string
}

export interface Space extends SymfonyRelation, Timestampable {
  id: string
  name: string
  description: string
  maxCapacity: number
  contact?: string
  email?: string
  videoLink?: string
  equipment?: string
  photos: BaseMediaObject[]
  highlights: SpaceHighlight[]
  slug: string
}

export interface SpaceHighlightSubmission extends Omit<Partial<SpaceHighlight>, 'report'> {
  report?: string
  space?: string
}