import { apiClient } from '@/plugins/axios/api'

export class PageViewService {
  static async logView(path: string): Promise<void> {
    try {
      await apiClient.post('/api/page_views', { path })
    } catch {
      // Le tracking ne doit jamais bloquer ni polluer l'expérience utilisateur en cas d'échec
    }
  }
}