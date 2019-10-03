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
        align="center"
      >
        <v-col
          cols="12"
          align="center"
        >
          <v-row justify="center">
            <v-col cols="3">
              <v-file-input
                v-model="avatar"
                :rules="rules"
                accept="image/png, image/jpeg, image/bmp"
                :label="$t('form.image.label')"
                prepend-icon="mdi-camera"
              />
            </v-col>
            <v-col cols="3">
              <v-text-field
                v-model="name"
                :label="$t('form.name.label')"
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
            <v-col cols="3">
              <v-text-field
                v-model="description"
                :label="$t('form.description.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col
              v-show="true"
              cols="3"
            >
              <v-checkbox
                v-model="membersHidden"
                :label="$t('form.checkboxes.membersHidden')"
                color="success"
              />
              <v-checkbox
                v-model="proposalsHidden"
                :label="$t('form.checkboxes.proposalsHidden')"
                color="success"
              />
            </v-col>
            <v-col cols="3">
              <v-textarea
                v-model="fullDescription"
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
      rules: [
        value => !value || value.size < 2000000 || "La taille de votre image ne doit pas dépassr 2MB",
      ],
      communityAddress: null,
      name: null,
      description: null,
      fullDescription: null,
      avatar: null,
      loading: false,
      membersHidden: false,
      proposalsHidden: false,
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
      newCommunity.append("proposalsHidden", this.proposalsHidden);
      newCommunity.append("membersHidden", this.membersHidden);
      newCommunity.append("privateCommunity", this.privateCommunity);

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