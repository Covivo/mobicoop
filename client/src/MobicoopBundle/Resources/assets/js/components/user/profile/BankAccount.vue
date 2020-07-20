<template>
  <div>
    <div v-if="loading">
      <v-skeleton-loader
        ref="skeleton"
        type="list-item-avatar-three-line"
        class="mx-auto mt-2"
      />        
    </div>
    <div v-else>
      <v-alert
        v-if="error"
        type="error"
      >
        {{ $t('error') }}
      </v-alert>        
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
                        @click.stop="dialog = true"
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

    <v-dialog
      v-model="dialog"
      max-width="400"
    >
      <v-card>
        <v-card-title class="headline">
          {{ $t('modalSuppr.title') }}
        </v-card-title>

        <v-card-text>
          <p>{{ $t('modalSuppr.line1') }}</p>
          <p>{{ $t('modalSuppr.line2') }}</p>
          <p>{{ $t('modalSuppr.line3') }}</p>
        </v-card-text>

        <v-card-actions>
          <v-spacer />

          <v-btn
            color="red darken-1"
            text
            @click="dialog = false"
          >
            {{ $t('modalSuppr.no') }}
          </v-btn>

          <v-btn
            color="green darken-1"
            text
            @click="deleteBankCoordinates()"
          >
            {{ $t('modalSuppr.yes') }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
      bankCoordinates:null,
      loading:false,
      title:this.$t('title'),
      dialog:false,
      error:false
    }
  },
  mounted(){
    this.getBankCoordinates();
  },
  methods:{
    getBankCoordinates(){
      this.loading = true;
      axios.post(this.$t("uri.getCoordinates"))
        .then(response => {
          //console.error(response.data);
          if(response.data){
            this.bankCoordinates = response.data[0];
            this.title = this.$t('titleAlreadyRegistered')
            this.loading = false;
          }
        })
        .catch(function (error) {
          console.error(error);
        });
    },
    deleteBankCoordinates(){
      this.dialog = false;
      this.loading = true;
      this.error = false;
      let params = {
        "bankAccountId":this.bankCoordinates.id
      }
      axios.post(this.$t("uri.deleteCoordinates"),params)
        .then(response => {
          if(response.data.error){
            this.error = true;
          }
          else{
            this.bankCoordinates = null;
          }
          this.loading = false;
        })
        .catch(function (error) {
          console.error(error);
        })
    }
  }
}
</script>