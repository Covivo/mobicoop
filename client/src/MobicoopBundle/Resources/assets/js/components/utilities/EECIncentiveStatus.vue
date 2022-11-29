<template>
  <div>
    <v-card color="grey lighten-4">
      <v-card-title
        class="text-center"
      >
        {{ $t('title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <h2
          class="mb-4"
        >
          {{ $t('subtitle') }}
        </h2>
        <p>
          {{ $t('intro') }}
        </p>
        <p>{{ $t('title_list') }}</p>
        <p
          v-for="(item, i) in items"
          :key="i"
        >
          <v-checkbox
            v-model="checkboxes"
            :label="item"
          />
        </p>
        <SsoLogins
          class="mt-5"
        />
        <p>
          <v-row align="center">
            <v-col
              cols="3"
              offset="3"
              class="text-right"
            >
              et identifiez-vous avec
            </v-col>
            <v-col
              cols="2"
              class="text-left"
            >
              <v-img
                contain
                src="/images/logo-france-connect.png"
              />
            </v-col>
          </v-row>
        </p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import SsoLogins from '@components/user/SsoLogins';
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/EECIncentiveStatus/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components:{
    SsoLogins
  },
  props: {
  },
  data() {
    return {
      items: this.$t('items'),
      checkboxes: []
    }
  },
};
</script>

