<script setup>
  import dayjs from 'dayjs'

  const props = defineProps({
    ticket: {}
  });

  const date = dayjs(props.ticket.created_at);

  console.log('[date]', dayjs().diff(date, 'minutes'))
</script>

<template>
  <div class="border-gray-200 border rounded-md p-2">
    <div class="flex items-center"><b>Id : </b> <i>{{ ticket.uuid }}</i></div>
    <div class="flex items-center"><b>Nombre :</b> <i>{{ ticket.name }}</i></div>
  </div>
</template>

<style>
  
</style>