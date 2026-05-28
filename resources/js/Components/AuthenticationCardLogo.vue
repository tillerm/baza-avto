<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" aria-label="БазаАвто">
        <img
            src="/images/logo-wide.png"
            alt="БазаАвто"
            class="block h-16 w-auto"
        />
    </Link>
</template>
