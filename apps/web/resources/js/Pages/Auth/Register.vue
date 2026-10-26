<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
const form = useForm({ name: '', organization_name: '', email: '', password: '', password_confirmation: '' });
</script>

<template>
  <main class="mx-auto max-w-lg p-6 pt-12">
    <div class="panel space-y-6">
      <div><p class="text-sm font-semibold text-indigo-700">NOTULEN AI</p><h1 class="mt-2 text-2xl font-bold">Buat workspace</h1><p class="mt-2 text-sm text-slate-600">Mulai dari ruang kerja pribadi untuk transkrip dan notulen.</p></div>
      <form class="space-y-4" @submit.prevent="form.post('/register')">
        <label class="block space-y-1"><span>Nama lengkap</span><input v-model="form.name" required maxlength="120" /><small class="text-red-700">{{ form.errors.name }}</small></label>
        <label class="block space-y-1"><span>Nama organisasi</span><input v-model="form.organization_name" required maxlength="120" /><small class="text-red-700">{{ form.errors.organization_name }}</small></label>
        <label class="block space-y-1"><span>Email</span><input v-model="form.email" type="email" required /><small class="text-red-700">{{ form.errors.email }}</small></label>
        <label class="block space-y-1"><span>Kata sandi (minimal 12 karakter)</span><input v-model="form.password" type="password" autocomplete="new-password" required minlength="12" /><small class="text-red-700">{{ form.errors.password }}</small></label>
        <label class="block space-y-1"><span>Ulangi kata sandi</span><input v-model="form.password_confirmation" type="password" autocomplete="new-password" required /><small class="text-red-700">{{ form.errors.password_confirmation }}</small></label>
        <button class="btn w-full" type="submit" :disabled="form.processing">Daftar</button>
      </form>
      <p class="text-sm">Sudah punya akun? <Link href="/login" class="text-indigo-700 underline">Masuk</Link></p>
    </div>
  </main>
</template>
