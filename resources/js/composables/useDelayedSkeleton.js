import { ref } from 'vue'

export function useDelayedSkeleton(delay = 200) {
  const loading = ref(true)
  const showSkeleton = ref(false)

  let timer = null

  const start = () => {
    loading.value = true
    showSkeleton.value = false

    timer = setTimeout(() => {
      showSkeleton.value = true
    }, delay)
  }

  const finish = () => {
    clearTimeout(timer)
    loading.value = false
    showSkeleton.value = false
  }

  return {
    loading,
    showSkeleton,
    start,
    finish,
  }
}
