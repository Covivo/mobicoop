<template>
  <v-content>
    <v-container fluid>
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
              <UpdateProfile />
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
import UpdateProfile from "@js/components/user/UpdateProfile";
import ChangePassword from "@js/components/user/ChangePassword";
import MyProposals from "@js/components/user/MyProposals";

import { merge } from "lodash";
import Translations from "@translations/components/Profile.json";
import TranslationsClient from "@clientTranslations/components/Profile.json";

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
  props: {},
  data() {
    return {};
  }
};
</script>