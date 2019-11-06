<template>
  <v-container class="py-0">
    <v-row
      class="primary darken-2"
    >
      <v-icon
        v-if="isDriver"
        class="accent darken-2 pa-1 px-3 white--text"
      >
        mdi-car
      </v-icon>
      <v-divider
        v-if="isDriver && isPassenger"
        vertical
      />
      <v-icon
        v-if="isPassenger"
        class="secondary darken-2 pa-1 px-3 white--text"
      >
        mdi-walk
      </v-icon>
      <v-spacer />
      <v-btn
        class="secondary my-1"
        icon
      >
        <v-icon
          class="white--text"
        >
          mdi-delete-outline
        </v-icon>
      </v-btn>
      <v-btn
        class="secondary ma-1"
        icon
      >
        <v-icon class="white--text">
          mdi-pencil
        </v-icon>
      </v-btn>
      <v-btn
        v-if="isPausable"
        class="secondary my-1 mr-1"
        icon
      >
        <v-icon class="white--text">
          mdi-pause
        </v-icon>
      </v-btn>
    </v-row>
  </v-container>
</template>

<script>
export default {
  props: {
    isDriver: {
      type: Boolean,
      default: false
    },
    isPassenger: {
      type: Boolean,
      default: false
    },
    isPausable: {
      type: Boolean,
      default: true
    }
  }
}
</script>

<style scoped>

</style>