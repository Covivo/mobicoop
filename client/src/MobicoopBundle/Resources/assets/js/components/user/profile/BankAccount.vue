<template>
  <div>
    <v-form
      v-if="!bankCoordinates"
      v-model="valid"
    >
      <v-container>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
            class="text-center text-h6 pt-4"
          >
            {{ $t('title') }}
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
          >
            <v-text-field
              v-model="form.iban"
              :counter="34"
              :label="$t('iban')"
              required
            />        
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
          >
            <v-text-field
              v-model="form.bic"
              :counter="11"
              :label="$t('bic')"
              required
            />        
          </v-col>
        </v-row>
        <v-row justify="center">
          <v-col
            cols="12"
            md="10"
            class="text-center"
          >
            <v-btn 
              rounded
              color="secondary" 
              class="mt-4 justify-self-center"
            >
              {{ $t('register') }}
            </v-btn>
          </v-col>
        </v-row>      
      </v-container>
    </v-form>
    <div v-else>
      <v-row justify="center">
        <v-col
          cols="12"
          md="10"
          class="text-center text-h6 pt-4"
        >
          {{ $t('titleAlreadyRegistered') }}
        </v-col>
      </v-row>
      <v-row justify="center">
        <v-col cols="8">
          <v-card
            class="pa-2"
            flat
            color="blue-grey lighten-5"
          >
            <v-row>
              <v-col cols="10">
                <v-row>
                  <v-col cols="12">
                    <label class="caption">{{ $t('iban') }}</label> {{ bankCoordinates.iban }}
                  </v-col>
                </v-row>
                <v-row>
                  <v-col cols="12">
                    <label class="caption">{{ $t('bic') }}</label> {{ bankCoordinates.bic }}
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="2">
                <v-row align="center">
                  <v-col
                    cols="12"
                  >
                    <v-btn
                      class="secondary my-1"
                      icon
                    >
                      <v-icon
                        class="white--text"
                      >
                        mdi-delete-outline
                      </v-icon>
                    </v-btn>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card>
        </v-col>
      </v-row>
    </div>
  </div>
</template>
<script>
import axios from "axios";
import Translations from "@translations/components/user/profile/BankAccount.json";

export default {
  i18n: {
    messages: Translations
  },
  props: {
    user: {
      type: Object,
      default: () => {}
    }
  },
  data () {
    return {
      valid: false,
      form: {
        iban:"",
        bic:""
      },
      bankCoordinates:null
    }
  },
  mounted(){
    this.getBankCoordinates();
  },
  methods:{
    getBankCoordinates(){
       
      let params = {
        "userId":this.user.id
      }
      axios.post(this.$t("coordinatesUri"),params)
        .then(response => {
          //console.error(response.data);
          if(response.data){
            this.bankCoordinates = response.data[0];
          }
        })
        .catch(function (error) {
          console.error(error);
        });

      return []
    }
  }
}
</script>