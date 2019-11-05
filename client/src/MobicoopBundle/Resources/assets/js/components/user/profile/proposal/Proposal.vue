<template>
  <v-container>
    <v-card>
      <v-container class="pt-0">
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
            class="secondary my-1 mr-1"
            icon
          >
            <v-icon class="white--text">
              mdi-pause
            </v-icon>
          </v-btn>
        </v-row>
      </v-container>
      
      <v-card-text>
        <v-row />
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
export default {
  props: {
    proposal: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    isDriver () {
      return !!this.proposal.outward.criteria.driver;
    },
    isPassenger () {
      return !!this.proposal.outward.criteria.passenger;
    }
  }
}
</script>

<style scoped lang="scss">
.circle {
    border-radius: 50%;
}
</style>