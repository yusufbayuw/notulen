<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
const form = useForm({ email: '', password: '' });
</script>

<template>
  <main class="mx-auto max-w-lg p-6 pt-20">
    <div class="panel space-y-6">
      <div><p class="text-sm font-semibold text-indigo-700">NOTULEN AI</p><h1 class="mt-2 text-2xl font-bold">Masuk ke workspace</h1></div>
      <form class="space-y-4" @submit.prevent="form.post('/login')">
        <label class="block space-y-1"><span>Email</span><input v-model="form.email" type="email" autocomplete="username" required /><small class="text-red-700">{{ form.errors.email }}</small></label>
        <label class="block space-y-1"><span>Kata sandi</span><input v-model="form.password" type="password" autocomplete="current-password" required /><small class="text-red-700">{{ form.errors.password }}</small></label>
        <button class="btn w-full" type="submit" :disabled="form.processing">Masuk</button>
      </form>
      <p class="text-sm">Belum memiliki akun? <Link href="/register" class="text-indigo-700 underline">Buat workspace</Link></p>
    </div>
  </main>
</template>
