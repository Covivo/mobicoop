<template>
  <v-col
    cols="4"
  >
    <v-card
      elevation="2"
      class="mx-auto my-3 text-left"
      min-height="350px"
    >
      <v-card-title class="primary--text font-weight-bold">
        <h2> {{ title }} </h2>
      </v-card-title>

      <v-card-text class="black--text">
        {{ text }}
      </v-card-text>
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