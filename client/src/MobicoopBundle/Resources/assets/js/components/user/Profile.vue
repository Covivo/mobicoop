<template>
  <v-content>
    <v-container fluid>
      <v-row 
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('tabs.myProfile') }}</h1>
        </v-col>
      </v-row>
      <v-layout
        justify-center
        text-center
      >
        <v-flex xs10>
          <v-tabs
            slider-color="secondary"
            color="secondary"
            grow
          >
            <v-tab>{{ $t("tabs.myProfile") }}</v-tab>
            <v-tab-item>
              <UpdateProfile
                :user="user"
                :geo-search-url="geoSearchUrl"
                :age-min="ageMin"
                :age-max="ageMax"
              />
            </v-tab-item>
            <v-tab>{{ $t("tabs.password") }}</v-tab>
            <v-tab-item>
              <ChangePassword />
            </v-tab-item>
            <v-tab>{{ $t("tabs.myProposals") }}</v-tab>
            <v-tab-item>
              <MyProposals />
            </v-tab-item>
          </v-tabs>
        </v-flex>
      </v-layout>
    </v-container>
  </v-content>
</template>
<script>
import UpdateProfile from "@components/user/UpdateProfile";
import ChangePassword from "@components/user/ChangePassword";
import MyProposals from "@components/user/MyProposals";

import { merge } from "lodash";
import Translations from "@translations/components/user/Profile.json";
import TranslationsClient from "@clientTranslations/components/user/Profile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged
  },
  components: {
    UpdateProfile,
    ChangePassword,
    MyProposals
  },
  props: {
    user: {
      type: Object,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    }
  },
  data() {
    return {

    };
  }
};
</script>