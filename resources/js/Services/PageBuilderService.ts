import axios from '@/bootstrap'

class PageBuilderService {
  async getPages(filters = {}, page = 1) {
    return axios.get('/page-builder', { params: { page, ...filters } })
  }

  async getPage(pageId: number) {
    return axios.get(`/page-builder/${pageId}/edit`)
  }

  async createPage(data: any) {
    return axios.post('/page-builder', data)
  }

  async updatePage(pageId: number, data: any) {
    return axios.patch(`/page-builder/${pageId}`, data)
  }

  async deletePage(pageId: number) {
    return axios.delete(`/page-builder/${pageId}`)
  }

  async publishPage(pageId: number) {
    return axios.post(`/page-builder/${pageId}/publish`)
  }

  async unpublishPage(pageId: number) {
    return axios.post(`/page-builder/${pageId}/unpublish`)
  }

  async duplicatePage(pageId: number) {
    return axios.post(`/page-builder/${pageId}/duplicate`)
  }

  async getBlockTypes() {
    return axios.get('/page-builder/block-types')
  }

  async addBlock(pageId: number, data: any) {
    return axios.post(`/api/page-blocks/page/${pageId}`, data)
  }

  async updateBlock(blockId: number, data: any) {
    return axios.patch(`/api/page-blocks/${blockId}`, data)
  }

  async deleteBlock(blockId: number) {
    return axios.delete(`/api/page-blocks/${blockId}`)
  }

  async reorderBlocks(pageId: number, order: number[]) {
    return axios.post(`/api/page-blocks/page/${pageId}/reorder`, { order })
  }

  async duplicateBlock(blockId: number) {
    return axios.post(`/api/page-blocks/${blockId}/duplicate`)
  }

  async getSEOData(pageId: number) {
    return axios.get(`/api/page-seo/${pageId}`)
  }

  async updateSEOData(pageId: number, data: any) {
    return axios.patch(`/api/page-seo/${pageId}`, data)
  }
}

export default new PageBuilderService()
