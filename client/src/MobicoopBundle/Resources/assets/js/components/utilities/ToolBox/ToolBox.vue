<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
      >
        <h1 class="display-1 primary--text text-center font-weight-bold">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Translations from "@translations/components/article/ToolBox.json";
export default {
  i18n: {
    messages: Translations,
  },
  props: {},
  data() {
    return {
    }
  }
};
</script>
