<template>
  <v-row
    align="center"
    justify="end"
    class="min-width-no-flex"
  >
    <div v-if="user && displayPhone">
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
    <div v-if="displayMailBox">
      <v-btn
        color="secondary"
        small
        depressed
        fab
        class="ml-2"
      >
        <v-icon
          @click="openMailBox()"
        >
          mdi-email
        </v-icon>
      </v-btn>
    </div>
  </v-row>
</template>

<script>
// import formData from "../../../utils/request";

export default {
  props: {
    carpooler: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      default: null
    },
    ask: {
      type: Object,
      default: null
    },
    displayPhone: {
      type: Boolean,
      default: false
    },
    displayMailBox: {
      type: Boolean,
      default: false
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
    },
    openMailBox () {
      // let lParams = {
      //   idAsk: this.ask.id
      // };
      // formData(`${this.$t("utilisateur/messages")}`, lParams);
    }
  }
}
</script>

<style scoped>

</style>