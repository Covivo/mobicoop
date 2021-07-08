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

    <v-row align="center">
      <!-- image -->
      <v-col
        cols="3"
        align="center"
      >
        <v-img
          :src="(item['images'][0] && item['images'][0]['versions']['original']) ? item['images'][0]['versions']['original'] : item['defaultAvatar']"
          contain
          max-width="200"
          max-height="150"
        />
      </v-col>
      <v-col  
        xl="6"
        md="5"
      >
        <v-card-title>
          <div>
            <h4>
              <a :href="linkToCommunityShow(item)">{{ item.name }}</a><br>
              <v-chip
                v-if="item.nbMembers"
                color="secondary"
                small
                label
              >
                {{ $t('members', {members:item.nbMembers}) }}
              </v-chip>              
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
            class="mt-5"
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
        <v-card-title class="text-h5">
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
            {{ $t('no') }}
          </v-btn>
          <v-btn
            color="secondary darken-1"
            text
            @click="leaveCommunityDialog=false; postLeavingRequest()"
          >
            {{ $t('yes') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>
<script>

import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/community/CommunityListItem/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    },
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
      return this.$t('routes.community', {id:item.id, urlKey:item.urlKey});
    },
    postLeavingRequest() {
      this.loading = true;
      maxios
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
