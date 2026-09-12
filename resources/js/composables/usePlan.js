import { computed } from 'vue'
import { useUserStore } from '@/stores/user'

export function usePlan() {
  const userStore = useUserStore()

  const isCoachOrAdmin = computed(() =>
    ['coach', 'admin'].includes(userStore.userData?.role)
  )

  const isPro = computed(() =>
    isCoachOrAdmin.value || userStore.userData?.plan_id !== 1
  )

  const isFree = computed(() => !isPro.value)

  return { isPro, isFree, isCoachOrAdmin }
}
