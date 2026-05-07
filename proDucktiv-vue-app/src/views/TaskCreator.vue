<script setup>
import { ref } from 'vue'

const title = ref('')
const type = ref('')

const createdTasks = ref([])

function createTask() {
  if (!title.value || !type.value) {
    alert('Fill all fields')
    return
  }

  const newTask = {
    id: Date.now(),
    title: title.value,
    type: type.value,
  }

  createdTasks.value.push(newTask)

  title.value = ''
  type.value = ''
}
</script>

<template>
  <div>
    <h1>Task Creator Page</h1>

    <input
      v-model="title"
      type="text"
      placeholder="Task title"
    />

    <input
      v-model="type"
      type="text"
      placeholder="Task type"
    />

    <button @click="createTask">
      Create Task
    </button>

    <div
      v-for="task in createdTasks"
      :key="task.id"
    >
      <h2>{{ task.title }}</h2>

      <p>{{ task.type }}</p>

      <hr />
    </div>
  </div>
</template>