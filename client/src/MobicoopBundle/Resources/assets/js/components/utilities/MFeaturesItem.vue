<template>
  <v-col
    cols="4"
  >
    <v-card
      elevation="2"
      class="mx-auto my-3 text-left"
      min-height="374px"
    >
      <v-card-title class="success--text font-weight-bold">
        {{ title }}
      </v-card-title>

      <v-card-text>{{ text }}</v-card-text>
    </v-card>
  </v-col>
</template>
<script>
export default {
  props: {
    title: {
      type: String,
      default: ""
    },
    text: {
      type: String,
      default: ""
    }
  }    
}
</script>
<style lang="scss" scoped>
</style>