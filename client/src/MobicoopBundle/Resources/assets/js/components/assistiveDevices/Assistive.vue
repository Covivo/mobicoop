<template>
  <v-container>
    <v-row class="justify-center">
      <v-col
        cols="12"
        align="center"
      >
        <h1 class="primary--text">
          {{ $t('title') }}
        </h1>
      </v-col>
    </v-row>

    <!-- Solidary -->
    <v-row class="justify-center mb-10">
      <v-col
        cols="12"
        align="center"
      >
        <h2 class="secondary--text mb-8">
          {{ $t('solidary.title') }}
        </h2>
      </v-col>
      <v-col
        cols="12"
        align="center"
      >
        <v-btn
          rounded
          class="font-weight-bold mr-4"
          :href="$t('solidary.btn-1.href')"
        >
          {{ $t('solidary.btn-1.text') }}
        </v-btn>
        <v-btn
          rounded
          class="font-weight-bold"
          :disabled="!isUserAuthenticated"
          :href="$t('solidary.btn-2.href')"
        >
          {{ $t('solidary.btn-2.text') }}
        </v-btn>
      </v-col>
    </v-row>

    <!-- Assistive devices -->
    <v-row>
      <v-col
        align="center"
        cols="12"
        lg="8"
        offset-lg="2"
      >
        <h2 class="secondary--text mb-8">
          {{ $t('assistiveDevices.title') }}
        </h2>
        <div
          class="text-justify"
          v-html="$t('assistiveDevices.text')"
        />
      </v-col>
      <v-col
        cols="12"
        align="center"
      >
        <v-btn
          v-if="!isUserAuthenticated"
          class="font-weight-bold mr-4"
          rounded
          :href="$t('assistiveDevices.buttons.login.href')"
        >
          {{ $t('assistiveDevices.buttons.login.text') }}
        </v-btn>
        <v-btn
          v-if="!isUserAuthenticated"
          class="font-weight-bold"
          rounded
          :href="$t('assistiveDevices.buttons.signin.href')"
        >
          {{ $t('assistiveDevices.buttons.signin.text') }}
        </v-btn>
        <v-btn
          v-if="isUserAuthenticated && !isMobActivated"
          class="font-weight-bold mr-4"
          rounded
          @click="activateMob()"
        >
          {{ $t('assistiveDevices.buttons.activate.text') }}
        </v-btn>
        <v-btn
          v-if="isMobActivated"
          class="font-weight-bold"
          rounded
          :href="$t('assistiveDevices.buttons.assistiveConsult.href')"
        >
          {{ $t('assistiveDevices.buttons.assistiveConsult.text') }}
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/assistiveDevices";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/assistiveDevices";

let messagesMergedEn = merge(messages_en, messages_client_en);
let messagesMergedNl = merge(messages_nl, messages_client_nl);
let messagesMergedFr = merge(messages_fr, messages_client_fr);
let messagesMergedEu = merge(messages_eu, messages_client_eu);

export default {
  i18n: {
    messages: {
      'en': messagesMergedEn,
      'nl': messagesMergedNl,
      'fr': messagesMergedFr,
      'eu': messagesMergedEu
    }
  },
  props: {
    activationUri: {
      type: String,
      default: null
    },
    assistiveSsoProvider: {
      type: String,
      default: null
    },
    user: {
      type: Object,
      default: null
    },
  },
  computed: {
    isUserAuthenticated: function() {
      return this.user ? true : false;
    },
    isMobActivated: function() {
      return this.isUserAuthenticated
        && this.user.hasAccessToMobAPI;
    }
  },
  mounted() {
    this.activationUri = this.activationUri === '' ? null : this.activationUri;
  },
  methods: {
    activateMob: function () {
      if (!this.isMobActivated && this.activationUri) {
        window.location.href = new URL(this.activationUri);
      } else {
        alert(this.$t('errors.mob-activation'));
      }
    }
  }
}
</script>
