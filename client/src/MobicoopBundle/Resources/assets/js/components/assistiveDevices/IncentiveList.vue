<template>
  <v-container>
    <div v-if="!error">
      <v-row class="justify-center">
        <v-col
          cols="12"
          align="center"
        >
          <h1 class="primary--text">
            {{ $t('incentives.incentives.title') }}
          </h1>
        </v-col>
      </v-row>
      <v-row
        v-if="incentives.length"
        class="my-10 mx-16"
      >
        <v-col
          v-for="incentive in incentives"
          :key="incentive.id"
          cols="2"
        >
          <v-card
            style="min-height: 100%"
            class="d-flex flex-column"
          >
            <v-card-title>{{ incentive.title }}</v-card-title>
            <v-card-text class="truncated">
              {{ truncateText(incentive.description) }}
            </v-card-text>
            <v-card-actions class="mt-auto">
              <v-spacer />
              <v-btn
                class="my-3"
                color="secondary"
                small
                :href="$t('incentives.incentives.buttons.card.URI', {incentiveId: incentive.id})"
              >
                {{ $t('incentives.incentives.buttons.card.text') }}
              </v-btn>
              <v-spacer />
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
      <div v-else>
        <v-row
          v-if="downloadedData"
          class="mx-16"
        >
          <v-col
            cols="12"
          >
            {{ $t('incentives.incentives.no-content') }}
          </v-col>
        </v-row>
        <v-row
          v-else
          class="mx-16"
        >
          <v-col
            v-for="index in 3"
            :key="index"
            cols="2"
          >
            <v-skeleton-loader
              type="card"
            />
          </v-col>
        </v-row>
      </div>
    </div>
    <div
      v-else
      class="mt-10"
    >
      <v-alert
        border="left"
        colored-border
        type="error"
        elevation="2"
        class="mx-16"
      >
        {{ $t('incentives.incentives.messages.error') }}
      </v-alert>
    </div>
  </v-container>
</template>
<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/assistiveDevices";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
    resourcePath: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      error: false,
      incentives: [],
      downloadedData: false
    }
  },
  computed: {
  },
  mounted() {
    maxios.get(`/${this.resourcePath}`)
      .then(res => {
        this.incentives = res.data.sort((a, b) => {
          if (a.title < b.title) {
            return -1;
          }
          if (a.title > b.title) {
            return 1;
          }
          return 0;
        });
      })
      .error(err => {
        this.error = true;
      });
  },
  methods: {
    truncateText(text, length = 50) {
      return `${text.substring(0, length)}...`;
    }
  }
}
</script>
