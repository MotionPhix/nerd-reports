import { ref } from 'vue';

export function useProjectStatus(status: string) {

  const projectStatus = ref('');

  const getProjectStatus = () => {

    switch (status) {

      case 'in_progress':
        projectStatus.value = 'In progress';
        break;

      case 'completed':
        projectStatus.value = 'Completed';
        break;

      default:
        projectStatus.value = 'Unknown';
        break;
    }

  };

  getProjectStatus();

  return {

    projectStatus,

  };
}
