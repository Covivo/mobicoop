<template>
  <v-row
    align="center"
    justify="end"
    class="min-width-no-flex"
  >
    <div v-if="user && carpooler.phoneDisplay == 2">
      <v-btn
        v-show="!phoneButtonToggled"
        color="secondary"
        small
        depressed
        fab
        @click="toggleButton"
      >
        <v-icon>
          mdi-phone
        </v-icon>
      </v-btn>
      <v-btn
        v-show="phoneButtonToggled"
        color="secondary"
        small
        dark
        depressed
        rounded
        height="40px"
        @click="toggleButton"
      >
        <v-icon>mdi-phone</v-icon>
        {{ carpooler.phone }}
      </v-btn>
    </div>
    <!-- <div>
      <v-btn
        color="secondary"
        small
        depressed
        fab
        class="ml-2"
      >
        <v-icon
          @click="buttonAlert(inDev,$event);"
        >
          mdi-email
        </v-icon>
      </v-btn>
    </div> -->
  </v-row>
</template>

<script>
export default {
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    }
  },
  data () {
    return {
      phoneButtonToggled: false,
    }
  },
  methods: {
    toggleButton () {
      this.phoneButtonToggled = !this.phoneButtonToggled;
    }
  }
}
</script>

<style scoped>

</style>