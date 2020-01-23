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
        <h1>Les communautés</h1>
      </v-col>
    </v-row>
    <v-card>
      <v-tabs vertical>
        <v-tab>
          Qu'est-ce qu'une communauté?
        </v-tab>
        <v-tab>
          Une communauté, pour quoi faire?
        </v-tab>
        <v-tab>
          Qui peut créer une communauté?
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
              <p>
                {{ $t("whoCanCreate") }}
              </p>
            </v-card-text>
          </v-card>
        </v-tab-item>
      </v-tabs>
    </v-card>
  </v-container>
</template>
<script>
import { merge } from "lodash";
import Translations from "@translations/components/community/CommunityHelp.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityHelp.json";
let TranslationsMerged = merge(Translations, TranslationsClient);  
export default {
  i18n: {
    messages: TranslationsMerged
  }
}
</script>