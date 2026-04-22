// composables/useNavigationGuard.ts
import { ref, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

export function useNavigationGuard(initialValue = false) {
  const hasUnsavedChanges = ref<boolean>(initialValue)
  let beforeUnloadListener: ((e: BeforeUnloadEvent) => void) | null = null
  let inertiaNavigationInterceptor: (() => void) | null = null

  const setUnsavedChanges = (value: boolean) => {
    hasUnsavedChanges.value = value
    
    if (value) {
      // Add listeners if there are unsaved changes
      beforeUnloadListener = (e: BeforeUnloadEvent) => {
        if (!hasUnsavedChanges.value) return
        
        e.preventDefault()
        e.returnValue = ''
        return e.returnValue
      }
      window.addEventListener('beforeunload', beforeUnloadListener)

      inertiaNavigationInterceptor = router.on('before', (event: { 
        detail: { visit: { completed: boolean; interrupted: boolean } }; 
        preventDefault: () => void 
      }) => {
        if (hasUnsavedChanges.value && 
            !confirm('You have unsaved changes. Are you sure you want to leave this page?')) {
          event.preventDefault()
        }
      })
    } else {
      // Remove listeners if no unsaved changes
      if (beforeUnloadListener) {
        window.removeEventListener('beforeunload', beforeUnloadListener)
        beforeUnloadListener = null
      }
      if (inertiaNavigationInterceptor) {
        inertiaNavigationInterceptor()
        inertiaNavigationInterceptor = null
      }
    }
  }

  onBeforeUnmount(() => {
    if (beforeUnloadListener) {
      window.removeEventListener('beforeunload', beforeUnloadListener)
    }
    if (inertiaNavigationInterceptor) {
      inertiaNavigationInterceptor()
    }
  })

  return {
    hasUnsavedChanges,
    setUnsavedChanges
  }
}