import { computed, ref, watchEffect } from 'vue';

export default function useStickyBorder() {
  const isSticky = ref(false);

  watchEffect(() => {
    const handleScroll = () => {
      const section = document.querySelector('section.sticky');
      if (section) {
        const { top } = section.getBoundingClientRect();
        isSticky.value = top <= 40;
      }
    };

    window.addEventListener('scroll', handleScroll);

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  });

  const sectionClasses = computed(() => {
    return {
      'sticky': true,
      'top-10': true,
      'border rounded-full transition duration-300 dark:bg-gray-900': isSticky.value
    };
  });

  return {
    sectionClasses
  };
}
