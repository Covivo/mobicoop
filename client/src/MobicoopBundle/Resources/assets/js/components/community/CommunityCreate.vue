<template>
  <v-main>
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
            <v-col cols="6">
              <v-text-field
                v-model="name"
                :rules="nameRules"
                :label="$t('form.name.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-text-field
                v-model="description"
                :rules="descriptionRules"
                :label="$t('form.description.label')"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
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
            <v-col cols="6">
              <GeoComplete 
                :token="user ? user.token : ''"
                :url="geoSearchUrl"
                :label="$t('form.address.label')"
                @address-selected="addressSelected"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-tooltip
                left
                color="info"
                bottom
              >
                <template v-slot:activator="{ on }">
                  <v-text-field
                    v-model="domain"
                    :rules="domainRules"
                    :label="$t('form.domain.label')"
                    v-on="on"
                  />
                </template>
                <span>{{ $t('form.domain.tooltips') }}</span>
              </v-tooltip>
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-file-input
                v-model="avatar"
                :rules="avatarRules"
                accept="image/png, image/jpeg, image/bmp"
                :label="$t('form.avatar.label')"
                prepend-icon="mdi-image"
              />
            </v-col>
          </v-row>
          <v-row justify="center">
            <v-col cols="6">
              <v-btn
                rounded
                color="primary"
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
  </v-main>
</template>
<script>

import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityCreate/";
import GeoComplete from "@components/utilities/GeoComplete";
import maxios from "@utils/maxios";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
    avatarSize: {
      type: String,
      default: null
    }
  },
  data () {
    return {
      avatarRules: [
        v => !!v || this.$t("form.avatar.required"),
        v => !v || v.size < this.avatarSize || this.$t("form.avatar.size")+" (Max "+(this.avatarSize/1000000)+"MB)"
      ],
      communityAddress: null,
      name: null,
      nameRules: [
        v => !!v || this.$t("form.name.required"),
      ],
      description: null,
      descriptionRules: [
        v => !!v || this.$t("form.description.required"),
      ],
      fullDescription: null,
      fullDescriptionRules: [
        v => !!v || this.$t("form.fullDescription.required"),
      ],
      avatar: null,
      loading: false,
      snackError: null,
      snackbar: false,
      domain: null,
      domainRules: [
        v => !v || /([\w+-]*\.[\w+]*$)/.test(v) || this.$t("form.domain.error")
      ]
    }
  },
  methods: {
    addressSelected: function(address) {
      this.communityAddress = address;
    },
    createCommunity() {
      this.loading = true;
      if (this.name && this.description && this.fullDescription && this.avatar && this.communityAddress) {
        let newCommunity = new FormData();
        newCommunity.append("name", this.name);
        newCommunity.append("description", this.description);
        newCommunity.append("fullDescription", this.fullDescription);
        newCommunity.append("avatar", this.avatar);
        newCommunity.append("address", JSON.stringify(this.communityAddress));
        if (this.domain) newCommunity.append("domain", this.domain);

        maxios 
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
      } else {
        this.snackError = this.$t('error.community.required')
        this.snackbar = true;
        this.loading = false;
      }    
    },
  }
}
</script>

<style>

</style>