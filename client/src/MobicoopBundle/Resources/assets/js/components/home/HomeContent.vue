<template>
  <v-row
    justify="center"
  >
    <v-col
      v-if="communityDisplay"
      class="xs-12 md-4 lg-3 xl-2 d-flex justify-center"
    >
      <m-card
        background-color="secondary"
        text-color="white--text"
        :card-title="this.$t('community.title')"
        :card-text="this.$t('community.text')"
        :card-text-button="this.$t('community.button.label')"
        card-text-color-button="secondary--text"
        card-color-button="white"
        :href-button="this.$t('community.button.route')"
      />
    </v-col>
    <v-col
      v-if="eventDisplay"
      class="xs-12 md-4 lg-3 xl-2 d-flex justify-center"
    >
      <m-card
        background-color="secondary"
        text-color="white--text"
        :card-title="this.$t('event.title')"
        :card-text="this.$t('event.text')"
        :card-text-button="this.$t('event.button.label')"
        card-text-color-button="secondary--text"
        card-color-button="white"
        :href-button="this.$t('event.button.route')"
      />
    </v-col>
    <v-col
      v-if="solidaryDisplay"
      class="xs-12 md-4 lg-3 xl-2 d-flex justify-center"
    >
      <m-card
        background-color="secondary"
        text-color="white--text"
        :card-title="this.$t('solidary.title')"
        :card-text="this.$t('solidary.text')"
        :card-text-button="this.$t('solidary.button.label')"
        card-text-color-button="secondary--text"
        card-color-button="white"
        :href-button="this.$t('solidary.button.route')"
        :card-text-button-two="this.$t('solidary.button2.label')"
        card-text-color-button-two="secondary--text"
        card-color-button-two="white"
        :href-button-two="this.$t('solidary.button2.route')"
      />
    </v-col>
  </v-row>
</template>
<script>
import MCard from '@js/components/utilities/MCard'
import Translations from "@translations/components/home/HomeContent.json";

export default {
  i18n: {
    messages: Translations,
  },
  components: {
    MCard
  },
  props: {
    solidaryDisplay: {
      type: Boolean,
      default: false
    },
    eventDisplay: {
      type: Boolean,
      default: false
    },
    communityDisplay: {
      type: Boolean,
      default: false
    }
  },
  // props: {

  // }
}
</script>