<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import Card from "./components/Card";
import Loader from "./components/Loader.vue";

import dayjs from "dayjs";

const queue1 = ref([]);
const queue2 = ref([]);

onMounted(async () => {
  await initialLoad();

  setInterval(async () => {
    console.log("[verificate...]");
    const q1Ids = verificationDate(queue1, 2);
    const q2Ids = verificationDate(queue2, 3);

    console.log("[q1]", q1Ids);

    const totalIds = [...q1Ids, ...q2Ids];

    if (totalIds.length > 0) {
      await deleteTicket(totalIds);

      queue1.value = queue1.value.filter((t) => !q1Ids.includes(t.id));
      queue2.value = queue2.value.filter((t) => !q2Ids.includes(t.id));
    }
  }, 3000);
});

const form = reactive({
  uuid: "",
  name: "",
  queue_id: 1,
});

const isValid = computed(() => {
  return form.uuid.trim().length > 1 && form.name.trim().length > 1;
});

const isCreating = ref(false);

const initialLoad = async () => {
  try {
    const res = await axios("/api/tickets");
    console.log("[dataInitial]", res.data);

    queue1.value = res.data[0].tickets;
    queue2.value = res.data[1].tickets;
  } catch (error) {
    console.log("[Error]", error.response);
  }
};

const onSubmit = async () => {
  try {
    form.queue_id = calculateQueue();
    const res = await axios.post("/api/tickets", form);

    console.log("[Response]", res.data);

    if (form.queue_id == 1) {
      queue1.value.push(res.data);
    } else {
      queue2.value.push(res.data);
    }

    clearForm()

  } catch (error) {
    console.log("[Error]", error.response);
  }
};

const calculateQueue = () => {
  let queue1Minutes = queue1.value.length * 2;
  let queue2Minutes = queue2.value.length * 3;

  return queue2Minutes >= queue1Minutes ? 1 : 2;
};

const clearForm = () => {
  form.name = ''
  form.uuid = ''
  form.queue_id = 1
}

const verificationDate = (queue, minutes) => {
  let ids = []
  queue.value.map((t, i) => {
    let waitingTime = t.number_in_queue * minutes;
    let difference = dayjs().diff(dayjs(t.created_at), "minutes");

    if (difference >= waitingTime) {
      ids.push( t.id);
    }
  });
  return ids
};

const deleteTicket = async (ticketIds) => {
  const res = await axios.delete("/api/tickets", {data:{ ids: ticketIds }});
  console.log("[Deleted]", res.data);
};
</script>

<template>
  <main class="mx-[20%]">
    <div class="text-3xl text-white w-full text-center mt-8 font-bold">Ticket-App</div>
    <div class="grid grid-cols-12 p-8 text-white">
      <!-- form -->
      <div class="rounded-md border p-4 col-span-12">
        <form @submit.prevent="onSubmit">
          <input
            class="outline-none rounded-sm border mr-3 text-gray-600"
            placeholder="Id"
            type="text"
            v-model="form.uuid"
          />
          <input
            class="outline-none rounded-sm border mr-3 text-gray-600"
            placeholder="Nombre"
            type="text"
            v-model="form.name"
          />
          <button
            :disabled="!isValid"
            class="px-4 py-1 rounded-md bg-green-400 disabled:bg-gray-400"
          >
            <span v-if="!isCreating">Agregar</span>
            <Loader v-else />
          </button>
        </form>
      </div>

      <div
        class="col-span-12 text-2xl text-white w-full text-center mt-8 pb-4 border-white font-bold"
      >
        Colas
      </div>

      <!-- Colas -->
      <section class="col-span-12 p-4 border rounded-md grid grid-cols-12 gap-2">
        <div class="col-span-12 text-xl italic">Cola # 1</div>
        <div v-if="queue1.length === 0" class="col-span-12 italic text-xl text-center">
          No hay ningun ticket
        </div>
        <div v-for="ticket in queue1" class="col-span-3">
          <Card :ticket="ticket" />
        </div>
      </section>

      <section class="col-span-12 p-4 border rounded-md grid grid-cols-12 gap-2 mt-8">
        <div class="col-span-12 text-xl italic">Cola # 2</div>
        <div v-if="queue2.length === 0" class="col-span-12 italic text-xl text-center">
          No hay ningun ticket
        </div>
        <div v-for="ticket in queue2" class="col-span-3">
          <Card :ticket="ticket" />
        </div>
      </section>
    </div>
  </main>
</template>
