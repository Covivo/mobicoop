<template>
  <div>
    <v-row justify="center">
      <v-col
        cols="4"
        class="text-left"
      >
        <v-card :loading="loading">
          <v-card-title>{{ $t('title') }}</v-card-title>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
<script>
import Translations from "@translations/components/user/SsoLoginReturn.json";

export default {
  i18n: {
    messages: Translations,
  },
  props:{
    data:{
      type: Object,
      default: null
    }
  },
  data () {
    return {
      loading:true
    }
  },
}
</script>