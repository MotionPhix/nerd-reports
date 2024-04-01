import { computed, ref, watchEffect } from 'vue';

export default function useStickyTop() {
  const isSticky = ref(false);

  watchEffect(() => {
    const handleScroll = () => {
      const nav = document.querySelector('nav.sticky');
      if (nav) {
        const { top } = nav.getBoundingClientRect();
        isSticky.value = top <= 40;
      }
    };

    window.addEventListener('scroll', handleScroll);

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  });

  const navClasses = computed(() => {
    return {
      'sticky': true,
      'top-10': true,
      'border pl-4 border-gray-500 rounded-full transition duration-300 bg-gray-100 dark:bg-gray-900 z-40': isSticky.value
    };
  });

  return {
    navClasses
  };
}
