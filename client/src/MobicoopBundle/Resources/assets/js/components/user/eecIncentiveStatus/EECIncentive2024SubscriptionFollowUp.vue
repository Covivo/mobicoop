<template>
  <v-container>Trajet Longue distance</v-container>
</template>
<script>
export default {
  props: {
    type: {
      type: String,
      default: null
    },
    subscription: {
      type: Object,
      default: () => ({})
    },
    nbPendingProofs: {
      type: Number,
      default: 0
    },
    nbRejectedProofs: {
      type: Number,
      default: 0
    },
    nbValidatedProofs: {
      type: Number,
      default: 0
    },
  },
  mounted() {
    console.log(this.type);
  },
  methods: {
    isEecSubcsriptionTypeAllowed(type)
    {
      if (!type) {
        return false;
      }
    }
  }
}
</script>
