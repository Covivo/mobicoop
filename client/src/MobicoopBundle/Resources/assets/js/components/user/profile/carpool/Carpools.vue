<template>
  <v-container
    fluid
  >
    <v-row justify="center">
      <v-col>
        <v-tabs
          centered
          grow
        >
          <v-tab>{{ $t('carpools.ongoing') }}</v-tab>
          <v-tab-item>
            <v-container v-if="localAds.ongoing">
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
                <v-btn
                  color="secondary"
                  rounded
                  width="175px"
                  @click="getExport()"
                >
                  {{ $t('export') }}
                </v-btn>
              </v-row>
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
                <v-btn
                  color="secondary"
                  rounded
                  width="175px"
                  @click="getExport()"
                >
                  {{ $t('export') }}
                </v-btn>
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
              </v-row>
            </v-container>
          </v-tab-item>
        </v-tabs>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/profile/carpool/AcceptedCarpools.js";
import Carpool from "@components/user/profile/carpool/Carpool.vue";

export default {
  i18n: {
    messages: Translations,
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
  methods:{
    getExport(){
      axios.post(this.$t("exportUrl"))
        .then(res => {
          console.error(res.data)
        })
        .catch(function (error) {
          console.error(error);
        });
    }
  }
}
</script>

<style scoped>

</style>