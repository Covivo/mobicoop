<template>
  <v-container>
    <!-- Title and subtitle -->
    <v-row 
      justify="center"
    >
      <v-col
        cols="12"
        xl="10"
        align="center"
      >
        <h1 
          v-if="displayTitleCommunity"
        >
          {{ $t("title") }}
        </h1>
      </v-col>
    </v-row>
    <v-card outlined>
      <v-tabs vertical>
        <v-tab>
          {{ $t("firstQuestion") }}
        </v-tab>
        <v-tab>
          {{ $t("secondQuestion") }}
        </v-tab>
        <v-tab>
          {{ $t("thirdQuestion") }}
        </v-tab>

        <v-tab-item>
          <v-card flat>
            <v-card-text>
              <p>
                {{ $t("whatsCommunity") }}
              </p>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card flat>
            <v-card-text>
              <div>
                {{ $t("communityforwhat.title") }}
              </div>
              <br>
              <ul>
                <li>{{ $t("communityforwhat.list.firstLine") }}</li>
                <li>{{ $t("communityforwhat.list.secondLine") }}</li>
                <li>{{ $t("communityforwhat.list.thirdLine") }}</li>
                <li>{{ $t("communityforwhat.list.fourthLine") }}</li>   
              </ul>
            </v-card-text>
          </v-card>
        </v-tab-item>
        <v-tab-item>
          <v-card flat>
            <v-card-text>
              <p v-html="$t('whoCanCreate')" />
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </v-card>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityHelp/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/community/CommunityHelp/";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);  

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    }
  },
  props: {
    displayTitleCommunity: {
      type: Boolean,
      default: true
    }
  } 
}
</script>