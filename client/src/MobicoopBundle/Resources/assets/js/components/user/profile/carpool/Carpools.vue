<template>
  <v-container
    fluid
  >
    <v-row justify="center">
      <v-col>
        <v-row>
          <v-col
            cols="8"
            class="font-weight-bold text-h5"
          >
            {{ $t('needCarpoolProofs') }}
          </v-col>
          <v-col                   
            cols="8"
            class="font-italic text-caption"
          >
            {{ $t('clickAndGetFile') }}
          </v-col>
          <v-tooltip
            right
          >
            <template v-slot:activator="{ on }">
              <div
                v-on="disableExportButton && on"
              >
                <v-btn
                  color="secondary"
                  rounded
                  :disabled="disableExportButton" 
                  width="175px"
                  @click="getExport()"
                >
                  {{ $t('export') }}
                </v-btn>
              </div>
            </template>
            <span>{{ $t('tooltip') }}</span>
          </v-tooltip>
        </v-row>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('carpools.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.ongoing">
              <v-row
                v-for="ad in localAds.ongoing"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :user="user"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('carpools.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.archived">
              <v-row
                v-for="ad in localAds.archived"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import { merge } from "lodash";
import axios from "axios";
import {messages_fr, messages_en} from "@translations/components/user/profile/carpool/AcceptedCarpools/";
import {messages_client_fr, messages_client_en} from "@clientTranslations/components/user/profile/carpool/AcceptedCarpools/";
import Carpool from "@components/user/profile/carpool/Carpool.vue";

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);

export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'fr': MessagesMergedFr
    }
  },
  components: {
    Carpool
  },
  props: {
    acceptedCarpools: {
      type: Object,
      default: () => {}
    },
    user: {
      type: Object,
      default: null
    }
  },
  data(){
    return {
      localAds: this.acceptedCarpools
    }
  },
  computed: {
    disableExportButton() {
      let testOngoing = this.acceptedCarpools.ongoing.length == 0 || Object.keys(this.acceptedCarpools.ongoing).length == 0 ? true : false;
      let testArchived = this.acceptedCarpools.archived.length == 0 || Object.keys(this.acceptedCarpools.archived).length == 0 ? true : false;
      return testOngoing && testArchived;
    }
  },
  methods:{
    getExport(){
      axios.post(this.$t("exportUrl"))
        .then(res => {
          this.openFileDownload(res);
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    openFileDownload(response){
      const link = document.createElement('a');
      link.href = response.data;
      link.target = "_blank";
      document.body.appendChild(link);
      link.click();
    },
  }
}
</script>

<style scoped>

</style>