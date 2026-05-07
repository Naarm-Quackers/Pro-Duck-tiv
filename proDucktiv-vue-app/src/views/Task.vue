<script setup>
import { ref, computed } from 'vue'

const search = ref('')
const currentTab = ref('active')

const tasks = ref([
  {
    id: 1,
    title: 'Finish Assignment',
    type: 'Study',
    completed: false,
  },
  {
    id: 2,
    title: 'Attend Meeting',
    type: 'Meeting',
    completed: true,
  },
])

const filteredTasks = computed(() => {
  return tasks.value.filter((task) => {
    const matchesSearch = task.title
      .toLowerCase()
      .includes(search.value.toLowerCase())

    if (currentTab.value === 'active') {
      return !task.completed && matchesSearch
    }

    return task.completed && matchesSearch
  })
})

function toggleTask(task) {
  task.completed = !task.completed
}

function deleteTask(id) {
  tasks.value = tasks.value.filter(
    (task) => task.id !== id
  )
}
</script>

<template>
  <div>
    <h1>Task Page</h1>

    <input
      v-model="search"
      type="text"
      placeholder="Search tasks"
    />

    <button @click="currentTab = 'active'">
      Active
    </button>

    <button @click="currentTab = 'done'">
      Done
    </button>

    <div
      v-for="task in filteredTasks"
      :key="task.id"
    >
      <h2>{{ task.title }}</h2>

      <p>{{ task.type }}</p>

      <p>
        {{ task.completed ? 'Done' : 'Active' }}
      </p>

      <button @click="toggleTask(task)">
        Toggle
      </button>

      <button @click="deleteTask(task.id)">
        Delete
      </button>

      <hr />
    </div>
  </div>
</template>