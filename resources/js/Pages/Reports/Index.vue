<script setup lang="ts">
import NoContactFound from "@/Components/Contact/NoContactFound.vue"
import useStickyTop from "@/Composables/useStickyTop"
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue"
import { Head, Link } from "@inertiajs/vue3"
import { IconDownload, IconPlus } from "@tabler/icons-vue"
import { twi, twj } from "tw-to-css"

const props = defineProps<{
  reportData: Object,
  weekNumber?: string
}>()

const { navClasses } = useStickyTop()

defineOptions({ layout: AuthenticatedLayout })
</script>

<template>
  <Head title="Explore Reports" />

  <nav
    class="print:hidden flex items-center h-16 max-w-3xl gap-6 px-8 mx-auto dark:text-white dark:border-gray-700"
    :class="navClasses"
  >
    <h2
      class="flex items-center gap-2 text-xl font-semibold leading-tight text-gray-900 dark:text-white"
    >
      <span>Explore Reports</span>
    </h2>

    <span class="flex-1"></span>

    <a
      :href="route('reports.download')"
      class="inline-flex items-center gap-2 px-3 py-2 ml-6 font-semibold transition duration-300 rounded-md dark:text-slate-300 bg-slate-100 dark:bg-slate-800 dark:hover:text-slate-900 dark:hover:bg-slate-500 hover:bg-gray-200"
      download>
      <IconDownload stroke="2.5" class="w-4 h-4" />
      <span>Download</span>
    </a>
  </nav>

  <article class="max-w-3xl px-8 py-12 mx-auto">

    <section v-if="Object.keys(props.reportData).length">

      <!-- start here -->

      <p :style="twj('text-4xl font-display')">
        Weekly Report
      </p>

      <hr :style="twj('my-10 border-gray-300')" />

      <table style="width: 100%">
        <thead>

          <tr>
            <th></th>

            <th :style="twj('text-left text-lg')">
              Assigned by
            </th>

            <th :style="twj('text-left text-lg pl-[10px]')">
              Status
            </th>
          </tr>

        </thead>

        <template
          v-for="project in props.reportData" :key="project.project_id">

          <tbody
            style="margin-bottom: 5em; width: 100%;"
            v-if="project.tasks.length">

            <tr>
              <td style="width: 60%; border: none rgb(0, 0, 0)">

                <span :style="twj('text-xl font-display block')">

                  {{ project.project_name }}

                </span>

                <span :style="twj('text-sm block')">
                  For {{ project.project_contact.firm?.name ?? `${project.project_contact.first_name} ${project.project_contact.last_name}` }} | Week {{ props.weekNumber }}
                </span>

              </td>

              <td sty="width: 40%;" colspan="2"></td>
            </tr>

            <tr>
              <td style="width: 60%; border: none rgb(0, 0, 0)">
                <span><br/></span>
              </td>

              <td style="border: none rgb(0, 0, 0); width: 20%">
                <span><br/></span>
              </td>

              <td style="width: 20%; border: none rgb(0, 0, 0)">
                <span><br/></span>
              </td>
            </tr>

            <tr v-for="task in project.tasks" :key="task.task_id">
              <td
                style="
                  width: 60%;
                  padding: 10px;
                  border-left: none rgb(0, 0, 0);
                  border-right: none rgb(0, 0, 0);
                  border-bottom: none rgb(0, 0, 0);
                  vertical-align: top;
                "
                :style="twj('border-t border-gray-300')">
                <strong>
                  {{ task.task_name }}
                </strong>

                <div
                  v-html="task.task_info"
                  v-if="task.task_info"
                  :style="twj('text-sm text-gray-500')"></div>

                <div :style="twj('pt-2 text-sm mt-2 text-gray-400')">
                  {{ `${task.assigned_at} | ${task.actual_date}` }}
                </div>
              </td>

              <td
                style="
                  padding-top: 10px;
                  border-left: none rgb(0, 0, 0);
                  border-right: none rgb(0, 0, 0);
                  border-bottom: none rgb(0, 0, 0);
                  width: 20%;
                  vertical-align: top;
                "
                :style="twj('border-t border-gray-300')">
                <span>
                  {{ task.assigned_by }}
                </span>
              </td>

              <td
                style="
                  width: 20%;
                  padding-top: 10px;
                  padding-left: 10px;
                  border-left: none rgb(0, 0, 0);
                  border-right: none rgb(0, 0, 0);
                  border-bottom: none rgb(0, 0, 0);
                  vertical-align: top;
                "
                :style="twj('border-t border-gray-300')">
                <span>{{ task.status }}</span>
              </td>
            </tr>

            <tr>
              <td>
                <div style="margin-top: 5em; margin-bottom: 5em;"></div>
              </td>
            </tr>
          </tbody>

        </template>
      </table>

      <!-- end here -->
    </section>

    <section v-else class="w-full py-12 mt-12">
      <NoContactFound>
        <div>
          <Link
            :href="route('contacts.create')"
            class="flex gap-2 items-center text-gray-500 border-gray-500 border hover:border-gray-900 rounded-lg dark:border-slate-600 dark:text-gray-500 font-semibold my-4 px-3 py-1.5 dark:hover:text-gray-400 dark:hover:border-gray-400 hover:text-gray-900 transition duration-300"
            as="button"
          >
            <IconPlus class="w-5 h-5" stroke-width="3.5" />

            <span>Create contact</span>
          </Link>
        </div>
      </NoContactFound>
    </section>

  </article>
</template>
