<template>
  <v-content>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      color="error"
      top
    >
      {{ snackError }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-container>
      <v-row 
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t('title') }}</h1>
        </v-col>
      </v-row>
      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <v-row justify="center">
            <v-col cols="3">
              <v-text-field
                v-model="name"
                :rules="nameRules"
                :label="$t('form.name.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="3">
              <v-text-field
                v-model="description"
                :label="$t('form.description.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="3">
              <v-textarea
                v-model="fullDescription"
                :rules="fullDescriptionRules"
                :label="$t('form.fullDescription.label')"
                rows="5"
                auto-grow
                clearable
                outlined
                row-height="24"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="3">
              <GeoComplete 
                :url="geoSearchUrl"
                :label="$t('form.address.label')"
                @address-selected="addressSelected"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="3">
              <v-file-input
                v-model="avatar"
                :rules="avatarRules"
                accept="image/png, image/jpeg, image/bmp"
                :label="$t('form.avatar.label')"
                prepend-icon="mdi-inser-photo"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-btn
                rounded
                color="success"
                :loading="loading"
                @click="createCommunity"
              >
                {{ $t('buttons.create.label') }}
              </v-btn>
            </v-col>
          </v-row>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>
<script>

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/community/CommunityCreate.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityCreate.json";
import GeoComplete from "@components/utilities/GeoComplete";
import axios from "axios";

let TranslationsMerged = merge(Translations, TranslationsClient,CommonTranslations);

export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    GeoComplete
  },
  props:{
    user: {
      type: Object,
      default: null
    },
    community: {
      type: Array,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    sentToken: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      avatarRules: [
        v => !!v || this.$t("form.avatar.required"),
        v => !v || v.size < 1000000 || this.$t("form.avatar.size"),
      ],
      communityAddress: null,
      name: null,
      nameRules: [
        v => !!v || this.$t("form.name.required"),
      ],
      description: null,
      fullDescription: null,
      fullDescriptionRules: [
        v => !!v || this.$t("form.fullDescription.required"),
      ],
      avatar: null,
      loading: false,
      snackError: null,
      snackbar: false,
    }
  },
  methods: {
    addressSelected: function(address) {
      this.communityAddress = address;
    },
    createCommunity() {
      this.loading = true;
      let newCommunity = new FormData();
      newCommunity.append("name", this.name);
      newCommunity.append("description", this.description);
      newCommunity.append("fullDescription", this.fullDescription);
      newCommunity.append("avatar", this.avatar);
      newCommunity.append("address", JSON.stringify(this.communityAddress));

      axios 
        .post(this.$t('buttons.create.route'), newCommunity, {
          headers:{
            'content-type': 'multipart/form-data'
          }
        })
        .then(res => {
          if (res.data.includes('error')) {
            this.snackError = this.$t(res.data)
            this.snackbar = true;
            this.loading = false;
          }
          else window.location.href = this.$t('redirect.route');
        });
    },
  }
}
</script>

<style>

</style>