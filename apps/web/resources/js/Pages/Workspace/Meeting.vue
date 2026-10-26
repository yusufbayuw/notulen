<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
type Segment = { id: number; start_ms: number; end_ms: number; speaker_label: string | null; text: string };
type Summary = { overview?: string; decisions?: unknown[]; action_items?: unknown[] };
const props = defineProps<{ workspace: { name: string; slug: string }; meeting: { id: number; title: string; status: string }; segments: Segment[]; summary: Summary | null }>();
const minutes = (ms: number) => `${Math.floor(ms / 60000).toString().padStart(2, '0')}:${Math.floor((ms % 60000)/1000).toString().padStart(2, '0')}`;
function seek(ms: number) { const player = document.querySelector<HTMLAudioElement>('#meeting-audio'); if (player) { player.currentTime = ms / 1000; player.play(); } }
const audioUrl = `/w/${props.workspace.slug}/meetings/${props.meeting.id}/audio`;
</script>

<template>
  <main class="mx-auto max-w-6xl space-y-6 p-6">
    <Link :href="`/w/${workspace.slug}`" class="text-indigo-700 underline">← Kembali</Link>
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div><h1 class="text-3xl font-bold">{{ meeting.title }}</h1><p class="mt-1 text-sm text-slate-600">Status: {{ meeting.status }}</p></div>
      <button class="rounded-lg border px-4 py-2" @click="router.reload()">Perbarui status</button>
    </div>
    <audio id="meeting-audio" class="w-full" controls preload="metadata" :src="audioUrl"></audio>
    <div v-if="meeting.status === 'failed'" class="rounded-lg bg-rose-50 p-4 text-rose-800">Pemrosesan gagal. Hubungi administrator; kuota belum ditagihkan pada versi pengembangan.</div>
    <div v-else-if="meeting.status !== 'completed'" class="rounded-lg bg-indigo-50 p-4 text-indigo-900">AI sedang diproses dalam antrean. Klik Perbarui status untuk melihat hasil terbaru.</div>
    <div class="grid gap-5 md:grid-cols-2">
      <section class="panel space-y-4">
        <h2 class="text-xl font-semibold">Transkrip</h2>
        <p v-if="segments.length===0" class="text-sm text-slate-500">Belum ada transkrip.</p>
        <button v-for="s in segments" :key="s.id" class="block w-full border-b py-3 text-left last:border-0 hover:bg-slate-50" @click="seek(s.start_ms)">
          <span class="text-sm font-medium text-indigo-700">{{ minutes(s.start_ms) }} {{ s.speaker_label || '' }}</span>
          <p class="mt-1 text-sm leading-relaxed">{{ s.text }}</p>
        </button>
      </section>
      <section class="panel space-y-5">
        <h2 class="text-xl font-semibold">Ringkasan AI</h2>
        <div v-if="summary">
          <p class="whitespace-pre-line text-sm leading-7">{{ summary.overview }}</p>
          <h3 class="mt-6 font-semibold">Keputusan</h3>
          <ul class="mt-2 list-disc space-y-2 pl-5 text-sm"><li v-for="(item,i) in (summary.decisions || [])" :key="i">{{ typeof item === 'string' ? item : JSON.stringify(item) }}</li></ul>
          <h3 class="mt-6 font-semibold">Tindak lanjut</h3>
          <ul class="mt-2 list-disc space-y-2 pl-5 text-sm"><li v-for="(item,i) in (summary.action_items || [])" :key="i">{{ typeof item === 'string' ? item : JSON.stringify(item) }}</li></ul>
        </div>
        <p v-else class="text-sm text-slate-500">Ringkasan belum tersedia. LLM lokal perlu dijalankan dan dikonfigurasi secara terpisah.</p>
      </section>
    </div>
  </main>
</template>
