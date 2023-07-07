<template>
  <v-card
    v-if="isMessageDisplayed"
    color="info"
    flat
    dark
    max-height="50px"
    rounded="0"
  >
    <v-card-text>{{ getWarningMessage }}</v-card-text>
  </v-card>
</template>

<script>
export default {
  props: {
    carpoolersIdentity: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    getWarningMessage() {
      return 'Toto le clown';
    },
    isMessageDisplayed() {
      // Si un conducteur est inscrit CEE : dans tout fil de messages avec un passager dont l'identité n'est pas vérifiée
      // Si un conducteur n'est pas inscrit CEE : dans tout fil de messages, ajouter un bandeau en haut du fil
      // Pour un passager dont l'identité n'est pas vérifiée
      return true;
    },
  }
}
</script>
