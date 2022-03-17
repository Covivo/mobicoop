<template>
  <v-container>
    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="12">
        <v-img
          max-height="120px"
          contain 
          :src="$t('logo')"
          alt="logo picholines"
        />
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col
        cols="4"
      >
        <v-img
          :src="$t('intro.image.1')"
          alt="car"
        />
      </v-col>
      <v-col
        cols="4"
      >
        <h1
          class="text-h6 font-weight-bold text-left primary--text"
        >
          {{ $t('intro.title') }}
        </h1>
        <p>{{ $t('intro.text') }}</p>
      </v-col>
    </v-row>
    <v-row
      justify="center"
    >
      <v-col cols="8">
        <v-img
          :src="$t('intro.image.2')"
          max-height="120px"
          contain
        />
      </v-col>
    </v-row>

    
    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="8">
        <h2 class="text-center mt-10">
          {{ $t('title.1') }}
        </h2>
        <h3 class="text-center">
          {{ $t('subtitle.1') }}
        </h3>
        <p class="mt-5">
          {{ $t('paragraph.1') }}/p>
        </p>
        <h3 class="mt-10">
          {{ $t('section.1') }}
        </h3>
        <v-img
          :src="$t('image.1')"
          max-width="40%"
          alt="car"
        />
        <p
          class="mt-10"
          v-html="$t('paragraph.2')"
        />
      </v-col>
    </v-row>


    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="8">
        <h3>
          {{ $t('section.2') }}
        </h3>
        <p
          v-html="$t('paragraph.3')"
        />
      </v-col>
      <v-row
        align="center"
        justify="center"
      >
        <v-col cols="6">
          <v-img
            :src="$t('image.2')"
            contain
            alt="map"
          />
          <h2 class="text-center mt-10">
            {{ $t('title.2') }}
          </h2>
          <h3 class="text-center">
            {{ $t('subtitle.2') }}
          </h3>
        </v-col>
      </v-row>
    </v-row>
    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="8">
        <v-img
          :src="$t('picto.1')"
          max-width="8%"
        />
        <h4>
          {{ $t('section.3') }}
        </h4>
        <p>
          {{ $t('paragraph.4') }}
        </p>
        <p
          class="text-caption font-italic"
        >
          {{ $t('legend.1') }}
        </p>
      </v-col>
    </v-row>

    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="8">
        <v-img
          :src="$t('picto.2')"
          max-width="8%"
        />
        <h4>
          {{ $t('section.4') }}
        </h4>
        <p>
          {{ $t('paragraph.5') }}
        </p>
      </v-col>
    </v-row>

    <v-row
      align="center"
      justify="center"
    >
      <v-col cols="8">
        <v-img
          :src="$t('picto.3')"
          max-width="8%"
        />
        <h4>
          {{ $t('section.5') }}
        </h4>
        <p
          v-html="$t('paragraph.6')"
        />
      </v-col>
    </v-row>

    <v-row
      justify="center"
    >
      <v-col cols="8">
        <v-img
          :src="$t('image.3')"
          max-height="120px"
          contain
          alt="logos"
        />
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/utilities/Unknown/Unknown/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/utilities/Unknown/Unknown/";
let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);
export default {
  components:{
  },
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  props: {},
  data() {
    return {
    }
  }
};
</script>
