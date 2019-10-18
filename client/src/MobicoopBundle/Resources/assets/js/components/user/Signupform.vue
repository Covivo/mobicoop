<!--* Copyright (c) 2018, MOBICOOP. All rights reserved.-->
<!--* This project is dual licensed under AGPL and proprietary licence.-->
<!--***************************-->
<!--*    This program is free software: you can redistribute it and/or modify-->
<!--*    it under the terms of the GNU Affero General Public License as-->
<!--*    published by the Free Software Foundation, either version 3 of the-->
<!--*    License, or (at your option) any later version.-->
<!--*-->
<!--*    This program is distributed in the hope that it will be useful,-->
<!--*    but WITHOUT ANY WARRANTY; without even the implied warranty of-->
<!--*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the-->
<!--*    GNU Affero General Public License for more details.-->
<!--*-->
<!--*    You should have received a copy of the GNU Affero General Public License-->
<!--*    along with this program.  If not, see <gnu.org/licenses>.-->
<!--***************************-->
<!--*    Licence MOBICOOP described in the file-->
<!--*    LICENSE-->
<!--**************************-->

<template>
  <v-content>
    <v-container
      id="scroll-target"
      style="max-height: 500px"
      class="overflow-y-auto"
      fluid
    >
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
          cols="4"
          align="center"
        >
          <!--STEP 1 User identification-->
          <v-form
            ref="step 1"
            v-model="step1"
          >
            <v-text-field
              id="email"
              v-model="form.email"
              :rules="form.emailRules"
              :label="$t('models.user.email.placeholder')+` *`"
              name="email"
              required
            />
            <v-text-field
              v-model="form.telephone"
              :label="$t('models.user.phone.placeholder')+` *`"
              required
              :rules="form.telephoneRules"
              name="telephone"
              @keypress="isNumber(event)"
            />
            <v-text-field
              v-model="form.password"
              :append-icon="form.showPassword ? 'mdi-eye' : 'mdi-eye-off'"
              :rules="form.passwordRules"
              :type="form.showPassword ? 'text' : 'password'"
              name="password"
              :label="$t('models.user.password.placeholder')+` *`"
              required
              @click:append="form.showPassword = !form.showPassword"
            />
            <v-btn
              ref="button"
              rounded
              class="my-13"
              color="primary"
              :disabled="!step1"
              @click="$vuetify.goTo('#step2', options)"
            >
              {{ $t('ui.button.next') }}
            </v-btn>
          </v-form>

          <!--STEP 2 Name-->
          <v-form
            id="step2"
            ref="step 2"
            v-model="step2"
          >
            <v-text-field
              v-model="form.givenName"
              :rules="form.givenNameRules"
              :label="$t('models.user.givenName.placeholder')+` *`"
              class="givenName"
              required
              :disabled="!step1"
            />
            <v-text-field
              v-model="form.familyName"
              :rules="form.familyNameRules"
              :label="$t('models.user.familyName.placeholder')+` *`"
              class="familyName"
              required
              :disabled="!step1"
            />
            <v-row
              justify="center"
              align="center"
              class="mb-25"
            >
              <v-btn
                ref="button"
                rounded
                class="my-13 mr-12"
                color="primary"
                :disabled="!step1"
                @click="$vuetify.goTo('#step2', options)"
              >
                {{ $t('ui.button.previous') }}
              </v-btn>
              <v-btn
                ref="button"
                rounded
                class="my-13"
                color="primary"
                :disabled="!step2"
                @click="$vuetify.goTo('#step3', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-row>
          </v-form>

          <!--STEP 3 gender-->
          <v-form
            id="step3"
            ref="step 3"
            v-model="step3"
            :hidden="!step1"
          >
            <v-select
              v-model="form.gender"
              :items="form.genderItems"
              item-text="genderItem"
              item-value="genderValue"
              :rules="form.genderRules"
              :label="$t('models.user.gender.placeholder')+` *`"
              required
              :disabled="!step2"
            />
            <v-row
              justify="center"
              align="center"
              class="mb-40"
            >
              <v-btn
                ref="button"
                rounded
                class="my-13 mr-12"
                color="primary"
                :disabled="!step2"
                @click="$vuetify.goTo('#step3', options)"
              >
                {{ $t('ui.button.previous') }}
              </v-btn>
              <v-btn
                ref="button"
                rounded
                class="my-13"
                color="primary"
                :disabled="!step3"
                @click="$vuetify.goTo('#step4', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-row>
          </v-form>

          <!--STEP 4 birthyear-->
          <v-form
            id="step4"
            ref="step 4"
            v-model="step4"
            :hidden="!step2"
          >
            <v-select
              v-model="form.birthYear"
              :items="years"
              :rules="form.birthYearRules"
              :label="$t('models.user.birthYear.placeholder')+` *`"
              required
              :disabled="!step3"
            />
            <v-row
              justify="center"
              align="center"
              class="mb-40"
            >
              <v-btn
                ref="button"
                rounded
                class="my-13 mr-12"
                color="primary"
                :disabled="!step3"
                @click="$vuetify.goTo('#step4', options)"
              >
                {{ $t('ui.button.previous') }}
              </v-btn>
              <v-btn
                ref="button"
                rounded
                class="my-13"
                color="primary"
                :disabled="!step4"
                @click="$vuetify.goTo('#step5', options)"
              >
                {{ $t('ui.button.next') }}
              </v-btn>
            </v-row>
          </v-form>

          <!--STEP 5 hometown-->
          <v-form
            id="step5"
            ref="form"
            v-model="step5"
            :hidden="!step3"
          >
            <GeoComplete
              name="homeAddress"
              :label="$t('models.user.homeTown.placeholder')"
              :url="geoSearchUrl"
              :hint="$t('models.user.homeTown.hint')"
              persistent-hint
              :disabled="!step4"
              @address-selected="selectedGeo"
            />
            <v-checkbox
              v-model="form.validation"
              class="check"
              color="primary"
              :rules="form.checkboxRules"
              required
              :disabled="!step4"
            >
              <template
                v-slot:label
                v-slot:activator="{ on }"
              >
                <a
                  class="secondary--text"
                  target="_blank"
                  href="/cgu"
                  @click.stop
                >{{ $t('ui.pages.signup.chart.chartValid') }}
                </a>
              </template>
            </v-checkbox>
            <v-btn
              color="primary"
              rounded
              class="mr-4 mb-100 mt-12"
              :disabled="!step5 || loading"
              :loading="loading"
              @click="validate"
            >
              {{ $t('ui.button.register') }}
            </v-btn>
          </v-form>
        </v-col>
      </v-row>
    </v-container>
  </v-content>
</template>

<script>
import axios from "axios";
import GeoComplete from "@js/components/utilities/GeoComplete";

import { merge } from "lodash";
import CommonTranslations from "@translations/translations.json";
import Translations from "@translations/components/user/SignUp.json";
import TranslationsClient from "@clientTranslations/components/user/SignUp.json";

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
    sharedMessages: CommonTranslations
  },
  components: {
    GeoComplete,
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: null
    },
    sentToken: {
      type: String,
      default: null
    },
    ageMin: {
      type: String,
      default: null
    },
    ageMax: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      //
      event: null,
      loading: false,

      //step validators
      step1: true,
      step2: true,
      step3: true,
      step4: true,
      step5: true,


      //scrolling data
      type: 'selector',
      selected: null,
      duration: 1000,
      offset: 180,
      easing: "easeOutQuad",
      container: "scroll-target",

      form:{
        createToken: this.sentToken,
        email: null,
        emailRules: [
          v => !!v || this.$t("models.user.email.errors.required"),
          v => /.+@.+/.test(v) || this.$t("models.user.email.errors.valid")
        ],
        givenName: null,
        givenNameRules: [
          v => !!v || this.$t("models.user.givenName.errors.required"),
        ],
        familyName: null,
        familyNameRules: [
          v => !!v || this.$t("models.user.familyName.errors.required"),
        ],
        gender: null,
        genderRules: [
          v => !!v || this.$t("models.user.gender.errors.required"),
        ],
        genderItems: [
          { genderItem: this.$t('models.user.gender.values.female'), genderValue: '1' },
          { genderItem: this.$t('models.user.gender.values.male'), genderValue: '2' },
          { genderItem: this.$t('models.user.gender.values.other'),genderValue: '3' },
        ],
        birthYear: null,
        birthYearRules: [
          v => !!v || this.$t("models.user.birthYear.errors.required")
        ],
        telephone: null,
        telephoneRules:  [
          v => !!v || this.$t("models.user.phone.errors.required"),
          v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("models.user.phone.errors.valid")
        ],
        password: null,
        showPassword: false,
        passwordRules: [
          v => !!v || this.$t("models.user.password.errors.required")
        ],
        homeAddress:null,
        checkboxRules: [
          v => !!v || this.$t("ui.pages.signup.chart.errors.required")
        ]
      }
    };
  },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },
    //options of v-scroll
    options () {
      return {
        duration: this.duration,
        offset: this.offset,
        easing: this.easing,
        container: this.container,
      }
    }
  },
  mounted: function () {
    //get scroll target
    this.container = document.getElementById ( "scroll-target" )
  },
  methods: {
    selectedGeo(address) {
      this.form.homeAddress = address;
    },
    validate: function (e) {
      this.loading = true,
      axios.post('/utilisateur/inscription',
        {
          email:this.form.email,
          telephone:this.form.telephone,
          password:this.form.password,
          givenName:this.form.givenName,
          familyName:this.form.familyName,
          gender:this.form.gender,
          birthYear:this.form.birthYear,
          address:this.form.homeAddress
        },{
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(response=>{
          window.location.href = this.$t('urlRedirectAfterSignUp');
          //console.log(response);
        })
        .catch(function (error) {
          console.log(error);
        });
    },

    isNumber: function(evt) {
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (-(charCode < 48 || charCode > 57) && charCode !== 43) {
        evt.preventDefault();;
      } else {
        return true;
      }
    },
  }

};
</script>

<style>
  @-moz-document url-prefix() { /* Disable scrollbar for Firefox */
    html,body,v-container{
      scrollbar-width: none;
      scrollbar-color: transparent transparent;
    }
  }

  ::-webkit-scrollbar { /* Disable scrollbar for Chrome and Edge */
    width: 0px;
    background: transparent;
  }

  .my-13 {
    margin-bottom:  52px;
    margin-top:     52px;
  }

  .mb-100 {
    margin-bottom:  300px;
  }

  .mb-25 {
      margin-bottom:  100px;
  }
  .mb-40 {
    margin-bottom:  160px;
  }
</style>