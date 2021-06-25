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
          <v-tab>{{ $t('carpools.active') }}</v-tab>
          <v-tab-item>
            <v-container v-if="carpools.active">
              <v-row
                v-for="ad in carpools.active"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-tab-item>
          <v-tab>{{ $t('carpools.archived') }}</v-tab>
          <v-tab-item>
            <v-container v-if="carpools.archived">
              <v-row
                v-for="ad in carpools.archived"
                :key="ad.id"
              >
                <v-col cols="12">
                  <Carpool
                    :ad="ad"
                    :is-archived="true"
                    :user="user"
                    :payment-electronic-active="paymentElectronicActive"
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

import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/carpool/AcceptedCarpools/";
import Carpool from "@components/user/profile/carpool/Carpool.vue";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  components: {
    Carpool
  },
  props: {
    carpools: {
      type: Object,
      default: () => {}
    },
    user: {
      type: Object,
      default: null
    },
    paymentElectronicActive: {
      type: Boolean,
      default: false
    },
  },
  data(){
    return {
    }
  },
  computed: {
    disableExportButton() {
      return !this.carpools.active && !this.carpools.archived;
    }
  },
  methods:{
    getExport(){
      maxios.post(this.$t("exportUrl"))
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