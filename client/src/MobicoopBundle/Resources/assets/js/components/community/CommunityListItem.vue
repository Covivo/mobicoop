<template>
  <v-card v-if="item">
    <v-snackbar
      v-model="snackbar"
      :color="(axiosState)?'error':'warning'"
      top
    >
      {{ axiosState ? this.$t("snackbar.leaveCommunity.textError") : this.$t("snackbar.leaveCommunity.textOk") }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>

    <v-row>
      <!-- image -->
      <v-col cols="3">
        <v-img
          v-if="item['images'][0]"
          :src="item['images'][0]['versions']['square_250']"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="150"
        />
        <v-img
          v-else
          src="/images/avatarsDefault/avatar.svg"
          aspect-ratio="1"
          class="grey lighten-2"
          max-width="200"
          max-height="200"
        />
      </v-col>
      <v-col  
        xl="6"
        md="5"
      >
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToCommunityShow(item)">{{ item.name }}</a>
            </h4>
          </div>
        </v-card-title>
        <v-divider />
        <v-list dense>
          <v-list-item>
            <v-list-item-content>
              {{ item.description }}
            </v-list-item-content>
          </v-list-item>
        </v-list>
      </v-col>

      <!-- action -->
      <v-col
        class="text-center"
      >
        <div
          class="my-2"
        >
          <v-btn
            color="secondary"
            rounded
            :width="250"
            :href="linkToCommunityShow(item)"
          >
            {{ $t('communityDetails') }}
          </v-btn>

          <v-btn
            v-if="canLeave"
            class="mt-5"
            color="primary"
            rounded
            :loading="loading"
            @click="leaveCommunityDialog = true"
          >
            {{ $t('leaveCommunity.button') }}
          </v-btn>
        </div>
      </v-col>
    </v-row>

    <!--Confirmation Popup-->
    <v-dialog
      v-if="canLeave"
      v-model="leaveCommunityDialog"
      persistent
      max-width="500"
    >
      <v-card>
        <v-card-title class="headline">
          {{ $t('leaveCommunity.popup.title') }}
        </v-card-title>
        <v-card-text
          v-html="(item.proposalsHidden) ? $t('leaveCommunity.popup.content.isProposalsHidden') : $t('leaveCommunity.popup.content.isNotProposalsHidden')"
        />
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="primary darken-1"
            text
            @click="leaveCommunityDialog=false"
          >
            {{ $t('ui.common.no') }}
          </v-btn>
          <v-btn
            color="secondary darken-1"
            text
            @click="leaveCommunityDialog=false; postLeavingRequest()"
          >
            {{ $t('ui.common.yes') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
<script>

import axios from "axios";
import { merge } from "lodash";
import Translations from "@translations/components/community/CommunityListItem.json";
import TranslationsClient from "@clientTranslations/components/community/CommunityList.json";
let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
  },
  props:{
    item:{
      type: Object,
      default: null
    },
    canLeave:{
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      snackbar: false,
      textSnackbar: null,
      axiosState: null,
      loading: false,
      leaveCommunityDialog: false
    }
  },
  methods:{
    linkToCommunityShow: function (item) {
      if(item.isSecured && !item.isMember){
        return this.$t('routes.communitySecuredRegister', {id:item.id})
      }
      return this.$t('routes.community', {id:item.id});
    },
    postLeavingRequest() {
      this.loading = true;
      axios
        .post(this.$t('leaveCommunity.route',{id:this.item.id}),
          {
            headers: {
              'content-type': 'application/json'
            }
          })
        .then(response => {
          this.axiosState = response.data.state;

          // UPDATE PAGE
          if(!response.data.state) {
            this.updateCommunityList();
          }

          // DISPLAY SNACKBAR AND STOP LOADING
          this.snackbar = true;
          this.loading = false;
        });
    },
    updateCommunityList() {
      let communityListComponent = this.getParentComponent('community-list');
      if(communityListComponent) {
        communityListComponent.leaveCommunity(this.item);
      }
    },
    getParentComponent(componentTag) {
      let component = null;
      let parent = this.$parent;
      while (parent && !component) {
        if (parent.$options._componentTag === componentTag) {
          component = parent
        }
        parent = parent.$parent
      }

      return component;
    }
  }
}
</script>
