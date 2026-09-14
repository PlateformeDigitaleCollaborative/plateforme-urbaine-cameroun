export interface ContentViewEntry {
  path: string
  slug: string
  views: number
}

export interface ContentViewStats {
  topActors: ContentViewEntry[]
  topProjects: ContentViewEntry[]
}
