import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import PageBuilderService from '@/Services/PageBuilderService'

interface PageBuilderState {
  currentPage: any | null
  blocks: any[]
  blockTypes: any[]
  seoData: any | null
  isDirty: boolean
  isSaving: boolean
  selectedBlockId: string | null
  undoStack: any[]
  redoStack: any[]
  saveError: string | null
}

export const usePageBuilderStore = defineStore('pageBuilder', () => {
  // State
  const currentPage = ref<any>(null)
  const blocks = ref<any[]>([])
  const blockTypes = ref<any[]>([])
  const seoData = ref<any>(null)
  const isDirty = ref(false)
  const isSaving = ref(false)
  const selectedBlockId = ref<string | null>(null)
  const undoStack = ref<any[]>([])
  const redoStack = ref<any[]>([])
  const saveError = ref<string | null>(null)

  // Getters
  const selectedBlock = computed(() =>
    blocks.value.find(b => b.id === selectedBlockId.value)
  )

  const blocksSorted = computed(() => {
    const sorted = [...blocks.value]
    return sorted.sort((a, b) => (a.order ?? 0) - (b.order ?? 0))
  })

  const pageUrl = computed(() => {
    if (!currentPage.value) return ''
    return route('pages.show', currentPage.value.slug)
  })

  const isDraft = computed(() => currentPage.value?.status === 'draft')
  const isPublished = computed(() => currentPage.value?.status === 'published')
  const hasUnsavedChanges = computed(() => isDirty.value)

  // Actions
  async function loadPage(pageId: number) {
    try {
      const response = await PageBuilderService.getPage(pageId)
      currentPage.value = response.data
      blocks.value = response.data.blocks || []
      seoData.value = response.data.seo
      isDirty.value = false
      saveError.value = null
    } catch (error) {
      console.error('Failed to load page:', error)
      saveError.value = 'Failed to load page'
    }
  }

  async function loadBlockTypes() {
    try {
      const response = await PageBuilderService.getBlockTypes()
      blockTypes.value = response.data || response
    } catch (error) {
      console.error('Failed to load block types:', error)
    }
  }

  async function savePage() {
    if (!currentPage.value) return

    isSaving.value = true
    try {
      await PageBuilderService.updatePage(currentPage.value.id, {
        title: currentPage.value.title,
        slug: currentPage.value.slug,
        description: currentPage.value.description,
        featured_image: currentPage.value.featured_image,
      })
      isDirty.value = false
      saveError.value = null
    } catch (error) {
      console.error('Failed to save page:', error)
      saveError.value = 'Failed to save page'
    } finally {
      isSaving.value = false
    }
  }

  async function saveBlocks() {
    if (!currentPage.value) return

    isSaving.value = true
    try {
      // Blocks are saved individually via API
      isDirty.value = false
      saveError.value = null
    } catch (error) {
      console.error('Failed to save blocks:', error)
      saveError.value = 'Failed to save blocks'
    } finally {
      isSaving.value = false
    }
  }

  function addBlock(blockType: any) {
    const newBlock = {
      id: 'temp_' + Date.now(),
      page_id: currentPage.value.id,
      block_type_id: blockType.id,
      order: blocks.value.length,
      content: {},
      blockType,
    }
    blocks.value.push(newBlock)
    isDirty.value = true
    selectedBlockId.value = newBlock.id
  }

  function removeBlock(blockId: string) {
    const index = blocks.value.findIndex(b => b.id === blockId)
    if (index > -1) {
      blocks.value.splice(index, 1)
      isDirty.value = true
    }
  }

  function updateBlock(blockId: string, data: any) {
    const block = blocks.value.find(b => b.id === blockId)
    if (block) {
      Object.assign(block, data)
      isDirty.value = true
    }
  }

  function selectBlock(blockId: string | null) {
    selectedBlockId.value = blockId
  }

  function deselectBlock() {
    selectedBlockId.value = null
  }

  function reorderBlocks(newOrder: string[]) {
    const newBlocks = newOrder
      .map(id => blocks.value.find(b => b.id === id))
      .filter(Boolean) as any[]

    blocks.value = newBlocks.map((block, index) => ({
      ...block,
      order: index,
    }))

    isDirty.value = true
  }

  function duplicateBlock(blockId: string) {
    const block = blocks.value.find(b => b.id === blockId)
    if (block) {
      const newBlock = {
        ...JSON.parse(JSON.stringify(block)),
        id: 'temp_' + Date.now(),
        order: block.order + 1,
      }
      blocks.value.splice(block.order + 1, 0, newBlock)
      isDirty.value = true
    }
  }

  async function savePageSEO(seoData: any) {
    if (!currentPage.value) return

    isSaving.value = true
    try {
      const response = await PageBuilderService.updateSEOData(
        currentPage.value.id,
        seoData
      )
      seoData.value = response.data
      return response
    } catch (error) {
      console.error('Failed to save SEO data:', error)
      saveError.value = 'Failed to save SEO data'
    } finally {
      isSaving.value = false
    }
  }

  async function publishPage() {
    if (!currentPage.value) return

    isSaving.value = true
    try {
      const response = await PageBuilderService.publishPage(
        currentPage.value.id
      )
      currentPage.value = response.data
    } catch (error) {
      console.error('Failed to publish page:', error)
      saveError.value = 'Failed to publish page'
    } finally {
      isSaving.value = false
    }
  }

  async function unpublishPage() {
    if (!currentPage.value) return

    isSaving.value = true
    try {
      const response = await PageBuilderService.unpublishPage(
        currentPage.value.id
      )
      currentPage.value = response.data
    } catch (error) {
      console.error('Failed to unpublish page:', error)
      saveError.value = 'Failed to unpublish page'
    } finally {
      isSaving.value = false
    }
  }

  function markDirty() {
    isDirty.value = true
  }

  function markClean() {
    isDirty.value = false
  }

  function undo() {
    if (undoStack.value.length > 0) {
      const previousState = undoStack.value.pop()
      // Store current state in redo stack
      redoStack.value.push({
        blocks: [...blocks.value],
        currentPage: { ...currentPage.value },
      })
      // Restore previous state
      blocks.value = previousState.blocks
      currentPage.value = previousState.currentPage
    }
  }

  function redo() {
    if (redoStack.value.length > 0) {
      const nextState = redoStack.value.pop()
      // Store current state in undo stack
      undoStack.value.push({
        blocks: [...blocks.value],
        currentPage: { ...currentPage.value },
      })
      // Restore next state
      blocks.value = nextState.blocks
      currentPage.value = nextState.currentPage
    }
  }

  return {
    // State
    currentPage,
    blocks,
    blockTypes,
    seoData,
    isDirty,
    isSaving,
    selectedBlockId,
    undoStack,
    redoStack,
    saveError,

    // Getters
    selectedBlock,
    blocksSorted,
    pageUrl,
    isDraft,
    isPublished,
    hasUnsavedChanges,

    // Actions
    loadPage,
    loadBlockTypes,
    savePage,
    saveBlocks,
    addBlock,
    removeBlock,
    updateBlock,
    selectBlock,
    deselectBlock,
    reorderBlocks,
    duplicateBlock,
    savePageSEO,
    publishPage,
    unpublishPage,
    markDirty,
    markClean,
    undo,
    redo,
  }
})
