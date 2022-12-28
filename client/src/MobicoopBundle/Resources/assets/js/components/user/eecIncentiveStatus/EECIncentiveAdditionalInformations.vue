<template>
  <div>
    <v-card color="grey lighten-4">
      <v-card-title
        class="text-center"
      >
        {{ $t('additional.title') }}
      </v-card-title>
      <v-card-text class="text-center">
        <p class="font-weight-bold">
          {{ $t('additional.intro') }}
        </p>
        <p>
          <v-list class="text-left">
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('additional.mandatory1') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="loading ? 'silver' : hasBankCoordinates ? 'green' : 'red'">
                  {{ loading ? 'mdi-timer-sand-empty' : hasBankCoordinates ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
            <v-list-item>
              <v-list-item-content>
                <v-list-item-title>{{ $t('additional.mandatory2') }}</v-list-item-title>
              </v-list-item-content>

              <v-list-item-icon>
                <v-icon :color="loading ? 'silver' : validatedIdentity ? 'green' : 'red'">
                  {{ loading ? 'mdi-timer-sand-empty' : validatedIdentity ? 'mdi-check' : 'mdi-close' }}
                </v-icon>
              </v-list-item-icon>
            </v-list-item>
          </v-list>
        </p>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/EECIncentiveStatus/";

export default {
  i18n: {
    messages: {
      'en': messages_en,
      'nl': messages_nl,
      'fr': messages_fr,
      'eu':messages_eu
    }
  },
  props: {
  },
  data() {
    return {
      bankCoordinates: null,
      loading: false
    }
  },
  computed:{
    hasBankCoordinates(){
      if(!this.bankCoordinates || this.bankCoordinates.status == 0){
        return false;
      }
      return true;
    },
    validatedIdentity(){
      if(!this.hasBankCoordinates || this.bankCoordinates.validationStatus == 0 || this.bankCoordinates.validationStatus > 1){
        return false;
      }
      return true;
    }
  },
  mounted(){
    this.getBankCoordinates();
  },
  methods:{
    getBankCoordinates(){
      this.loading = true;
      maxios.post(this.$t("additional.uri.getCoordinates"))
        .then(response => {
          // console.error(response.data);
          if(response.data){
            if(response.data[0]) this.bankCoordinates = response.data[0];
            this.title = this.$t('titleAlreadyRegistered')
            this.loading = false;
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    },
  }

};
</script>

