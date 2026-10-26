<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
type Meeting = { id: number; title: string; status: string; language: string; created_at: string };
const props = defineProps<{ workspace: { id: number; name: string; slug: string }; meetings: Meeting[]; currentUser: { name: string } }>();
const form = useForm<{ title: string; language: string; audio: File | null }>({ title: '', language: 'id', audio: null });
function onFile(event: Event) { form.audio = (event.target as HTMLInputElement).files?.[0] || null; }
function submit() {
    form.post(`/w/${props.workspace.slug}/recordings`, { forceFormData: true });
}
</script>

<template>
  <main class="mx-auto max-w-5xl space-y-8 p-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
      <div><p class="font-semibold tracking-wide text-indigo-700">NOTULEN AI</p><h1 class="text-3xl font-bold">{{ workspace.name }}</h1><p class="text-slate-500">Halo, {{ currentUser.name }}</p></div>
      <Link href="/logout" method="post" as="button" class="rounded-lg border px-4 py-2">Keluar</Link>
    </header>
    <section class="panel space-y-4">
      <h2 class="text-xl font-semibold">Buat notulen baru</h2>
      <p class="text-sm text-slate-600">Unggah audio atau video (MP3, M4A, WAV, MP4, OGG, WebM). Maksimum 100 MB. Jangan merekam orang tanpa dasar izin yang sesuai.</p>
      <form class="grid gap-4" @submit.prevent="submit">
        <label>Judul rapat<input v-model="form.title" required maxlength="200" placeholder="Contoh: Rapat evaluasi Oktober" /></label>
        <label>Bahasa
          <select v-model="form.language" class="w-full rounded-lg border p-3"><option value="id">Indonesia</option><option value="en">English</option></select>
        </label>
        <label>Rekaman<input type="file" accept=".mp3,.m4a,.wav,.mp4,.ogg,.webm,audio/*,video/mp4" required @change="onFile" /></label>
        <p v-if="form.errors.audio" class="text-sm text-red-700">{{ form.errors.audio }}</p>
        <p v-if="form.progress" class="text-sm text-indigo-700">Mengunggah {{ form.progress.percentage }}%</p>
        <button class="btn" type="submit" :disabled="form.processing">Upload &amp; proses transkrip</button>
      </form>
    </section>
    <section class="space-y-3">
      <h2 class="text-xl font-semibold">Rapat terakhir</h2>
      <p v-if="meetings.length === 0" class="text-slate-600">Belum ada rekaman.</p>
      <Link v-for="m in meetings" :key="m.id" :href="`/w/${workspace.slug}/meetings/${m.id}`" class="panel block transition hover:border-indigo-400">
        <div class="flex items-center justify-between gap-3"><strong>{{ m.title }}</strong><span class="rounded-lg bg-slate-100 px-2 py-1 text-xs">{{ m.status }}</span></div>
        <div class="mt-2 text-sm text-slate-500">{{ new Date(m.created_at).toLocaleString('id-ID') }}</div>
      </Link>
    </section>
  </main>
</template>
