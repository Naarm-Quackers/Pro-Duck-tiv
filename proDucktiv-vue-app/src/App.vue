<script setup>
import Menu from './components/Menu.vue'
</script>

<template>
  <Menu />

  <nav>
    <RouterLink to="/task">
      Tasks
    </RouterLink>

    |

    <RouterLink to="/task-creator">
      Task Creator
    </RouterLink>
  </nav>

  <router-view />
</template>