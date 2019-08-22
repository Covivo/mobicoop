<template>
  <v-card
    height="215"
    flat
  >
    <result-journey-detailed-card />
    <v-divider class="divider-width" />
    <result-user-detailed-card />
  </v-card>
</template>
<script>
export default {
  name: "ResultCard"
}
</script>

<style scoped>
    .divider-width{
        max-width: 730px;
    }
</style>