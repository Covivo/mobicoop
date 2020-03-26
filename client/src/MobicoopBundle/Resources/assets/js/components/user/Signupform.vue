<!--* Copyright (c) 2020, MOBICOOP. All rights reserved.-->
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
  <div>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(errorUpdate)?'error': (communities.validationType == 1 ? 'warning' : 'success')"
      top
    >
      {{ textSnackbar }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-container
      id="scroll-target"
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
        v-if="showFacebookSignUp"
        justify="center"
        class="text-center"
      >
        <v-col class="col-4">
          <m-facebook-auth
            :app-id="facebookLoginAppId"
            :sign-up="true"
            @fillForm="fillForm"
          />
        </v-col>
      </v-row>

      <v-row
        justify="center"
        align="center"
      >
        <v-col
          cols="8"
          align="center"
        >
          <v-stepper 
            v-model="step"
            non-linear
          >
            <v-stepper-header
              class="elevation-0"
            >
              <!--STEP 1 User identification-->
              <v-stepper-step
                :step="1"
                editable
                edit-icon
              />
              <v-divider />

              <!--STEP 2 Name - Gender - Birthyear-->
              <v-stepper-step
                :step="2"
                editable
                edit-icon
              />
              <v-divider />

              <!--STEP 3 Community-->
              <v-stepper-step
                v-if="communityShow"
                :step="3"
                editable
                edit-icon
              />
              <v-divider v-if="communityShow" />


              <!--STEP 4 hometown-->
              <v-stepper-step
                :step="(communityShow) ? 4 : 3"
                editable
                edit-icon
              />
            </v-stepper-header>

            <!--STEP 1 User identification-->
        
            <v-stepper-content
              step="1"
            >
              <v-form
                ref="step 1"
                v-model="step1"
                class="pb-2"
                @submit.prevent
              >
                <v-text-field
                  id="email"
                  v-model="form.email"
                  :rules="form.emailRules"
                  :label="$t('models.user.email.placeholder')+` *`"
                  name="email"
                  required
                  :loading="loadingCheckEmailAldreadyTaken"
                  @focusout="checkEmail"
                  @focusin="emailAlreadyTaken = false"
                />
                <v-alert
                  v-if="emailAlreadyTaken"
                  type="error"
                >
                  {{ $t('checkEmail.error') }}
                </v-alert>

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
                  :rules="[form.passWordRules.required,form.passWordRules.min, form.passWordRules.checkUpper,form.passWordRules.checkLower,form.passWordRules.checkNumber]"
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
                  color="secondary"
                  type="submit"
                  @click="nextStep(1)"
                >
                  {{ $t('ui.button.next') }}
                </v-btn>
              </v-form>
            </v-stepper-content>
          


            <!--STEP 2 Name - Gender - Birthyear-->
        
            <v-stepper-content
              step="2"
            >
              <v-form
                id="step2"
                ref="step 2"
                v-model="step2"
                class="pb-2"
                @submit.prevent
              >
                <v-text-field
                  v-model="form.givenName"
                  :rules="form.givenNameRules"
                  :label="$t('models.user.givenName.placeholder')+` *`"
                  class="givenName"
                  required
                />
                <v-text-field
                  v-model="form.familyName"
                  :rules="form.familyNameRules"
                  :label="$t('models.user.familyName.placeholder')+` *`"
                  class="familyName"
                  required
                />

                <v-select
                  v-model="form.gender"
                  :items="form.genderItems"
                  item-text="genderItem"
                  item-value="genderValue"
                  :rules="form.genderRules"
                  :label="$t('models.user.gender.placeholder')+` *`"
                  required
                />
                <v-menu
                  ref="menu"
                  v-model="menu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      v-model="form.date"
                      :label="$t('models.user.birthYear.placeholder')+` *`"
                      readonly
                      :rules="[ form.birthdayRules.checkIfAdult, form.birthdayRules.required ]"
                      required
                      v-on="on"
                    />
                  </template>
                  <v-date-picker
                    ref="picker"
                    v-model="form.date"
                    :max="maxDate()"
                    :locale="locale"
                    first-day-of-week="1"
                    @change="save"
                  />
                </v-menu>
                <v-row
                  justify="center"
                  align="center"
                  class="mb-25"
                >
                  <v-btn
                    ref="button"
                    rounded
                    class="my-13 mr-12"
                    color="secondary"
                    @click="previousStep(2)"
                  >
                    {{ $t('ui.button.previous') }}
                  </v-btn>
                  <v-btn
                    ref="button"
                    rounded
                    class="my-13"
                    color="secondary"
                    type="submit"
                    @click="nextStep(2)"
                  >
                    {{ $t('ui.button.next') }}
                  </v-btn>
                </v-row>
              </v-form>
            </v-stepper-content>
          

            <!--STEP 3 Community-->
        
            <v-stepper-content
              v-if="communityShow"
              step="3"
            >
              <v-row
                class="text-justify pb-5"
              >
                <community-help />
              </v-row>
              <v-form
                id="step3"
                ref="form"
                v-model="step3"
                class="pb-2"
                @submit.prevent
              >
                <v-row
                  align="center"
                  justify="center"
                  class="mt-2"
                >
                  <v-col
                    cols="12"
                  >
                    <v-autocomplete                  
                      v-model="selectedCommunity"
                      :items="communities.communities"
                      outlined
                      chips
                      :label="$t('communities.label')"
                      item-text="name"
                      item-value="id"
                      @change="emitEvent"
                    >
                      <template v-slot:selection="data">
                        <v-chip                      
                          v-bind="data.attrs"
                          :input-value="data.selected"
                          close
                          @click="data.select"
                          @click:close="toggleSelected"
                        >
                          {{ data.item.name }}
                        </v-chip>
                      </template>
                      <template v-slot:item="data">
                        <template v-if="typeof data.item !== 'object'">
                          <v-list-item-content v-text="data.item" />
                        </template>
                        <template v-else>
                          <v-list-item-content>
                            <v-list-item-title v-html="data.item.name" />
                            <v-list-item-subtitle v-html="data.item.description" />
                          </v-list-item-content>
                        </template>
                      </template>
                    </v-autocomplete>
                    <v-row
                      justify="center"
                      align="center"
                      class="mb-40"
                    >
                      <v-btn
                        ref="button"
                        rounded
                        class="my-13 mr-12"
                        color="secondary"

                        @click="previousStep(3)"
                      >
                        {{ $t('ui.button.previous') }}
                      </v-btn>
                      <v-btn
                        ref="button"
                        rounded
                        class="my-13"
                        color="secondary"
                        type="submit"
                        @click="nextStep(3)"
                      >
                        {{ $t('ui.button.next') }}
                      </v-btn>
                    </v-row>
                  </v-col>
                </v-row>
              </v-form>
            </v-stepper-content>
          

            <!--STEP 4 hometown-->
            <v-stepper-content
              :step="(communityShow) ? 4 : 3"
            >
              <v-form
                id="step4"
                ref="form"
                v-model="step4"
                class="pb-2"
                @submit.prevent
              >
                <GeoComplete
                  name="homeAddress"
                  :label="$t('models.user.homeTown.placeholder')"
                  :url="geoSearchUrl"
                  :hint="requiredHomeAddress ? $t('models.user.homeTown.required.hint') : $t('models.user.homeTown.hint')"
                  persistent-hint
                  :required="requiredHomeAddress"
                  @address-selected="selectedGeo"
                />
                <v-checkbox
                  v-model="form.validation"
                  class="check mt-12"
                  color="primary"
                  :rules="form.checkboxRules"
                  required
                >
                  <template
                    v-slot:label
                    v-slot:activator="{ on }"
                  >
                    <div>
                      {{ $t('chart.text') }}
                      <a
                        class="primary--text"
                        target="_blank"
                        :href="$t('chart.route')"
                        @click.stop
                      >{{ $t('chart.link') }}
                      </a>
                    </div>:
                  </template>
                </v-checkbox>
                <v-row
                  justify="center"
                  align="center"
                  class="mb-40"
                >
                  <v-btn
                    ref="button"
                    rounded
                    class="my-13 mr-12 mt-12 "
                    color="secondary"
                    @click="--step"
                  >
                    {{ $t('ui.button.previous') }}
                  </v-btn>
                  <v-btn
                    color="secondary"
                    rounded
                    class="mr-4 mb-100 mt-12"
                    :loading="loading"
                    :disabled="!step4 || !step3 || !step2 || !step1 || loading || isDisable"
                    @click="validate"
                  >
                    {{ $t('ui.button.register') }}
                  </v-btn>
                </v-row>
              </v-form>
            </v-stepper-content>
          </v-stepper>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import axios from "axios";
import GeoComplete from "@js/components/utilities/GeoComplete";
import CommunityHelp from "@components/community/CommunityHelp";

import { merge } from "lodash";
import Translations from "@translations/components/user/SignUp.json";
import TranslationsClient from "@clientTranslations/components/user/SignUp.json";
import MFacebookAuth from '@components/user/MFacebookAuth';

let TranslationsMerged = merge(Translations, TranslationsClient);
export default {
  i18n: {
    messages: TranslationsMerged,
  },
  components: {
    GeoComplete,
    MFacebookAuth,
    CommunityHelp
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
    },
    showFacebookSignUp: {
      type: Boolean,
      default: false
    },
    facebookLoginAppId: {
      type: String,
      default: null
    },
    requiredHomeAddress:  {
      type: Boolean,
      default: false
    },
    communityShow: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      step: 1,
      event: null,
      loading: false,
      //snackbar
      snackbar: false,
      errorUpdate: false,
      textSnackbar: null,
      textSnackOk: this.communities.validationType == 1 ? this.$t("snackbar.joinCommunity.textOkManualValidation") : this.$t("snackbar.joinCommunity.textOkAutoValidation"),

      //step validators
      step1: true,
      step2: true,
      step3: true,
      step4: true,
      step5: true,
      menu : false,

      //scrolling data
      type: 'selector',
      selected: null,
      duration: 1000,
      offset: 180,
      easing: "easeOutQuad",
      container: "scroll-target",

      emailAlreadyTaken : false,
      loadingCheckEmailAldreadyTaken: false,
      form: {
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
          {genderItem: this.$t('models.user.gender.values.female'), genderValue: '1'},
          {genderItem: this.$t('models.user.gender.values.male'), genderValue: '2'},
          {genderItem: this.$t('models.user.gender.values.other'), genderValue: '3'},
        ],
        date : null,
        telephone: null,
        telephoneRules: [
          v => !!v || this.$t("models.user.phone.errors.required"),
          v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("models.user.phone.errors.valid")
        ],
        password: null,
        showPassword: false,
        passWordRules: {
          required:  v => !!v || this.$t("models.user.password.errors.required"),
          min: v => (v && v.length >= 8 ) || this.$t("models.user.password.errors.min"),
          checkUpper : value => {
            const pattern = /^(?=.*[A-Z]).*$/
            return pattern.test(value) || this.$t("models.user.password.errors.upper")

          },
          checkLower : value => {
            const pattern = /^(?=.*[a-z]).*$/
            return pattern.test(value) || this.$t("models.user.password.errors.lower")

          },
          checkNumber : value => {
            const pattern = /^(?=.*[0-9]).*$/
            return pattern.test(value) || this.$t("models.user.password.errors.number")

          },
        },
        birthdayRules : {
          required:  v => !!v || this.$t("models.user.birthDay.errors.required"),
          checkIfAdult : value =>{
            var d1 = new Date();
            var d2 = new Date(value);

            var diff =(d1.getTime() - d2.getTime()) / 1000;
            diff /= (60 * 60 * 24);

            var diffYears =  Math.abs(Math.floor(diff/365.24) ) ;
            return diffYears >= 16 || this.$t("models.user.birthDay.errors.notadult")
          }
        },
        homeAddress:null,
        checkboxRules: [
          v => !!v || this.$t("ui.pages.signup.chart.errors.required")
        ],
        idFacebook:null
      },
      communities:[],
      selectedCommunity: this.communities,
      locale: this.$i18n.locale
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
    },
    // disable validation if homeAddress is empty and required or email already taken
    isDisable() {
      if (this.requiredHomeAddress && !this.form.homeAddress) {
        return true;
      }
      if(this.emailAlreadyTaken){
        return true;
      }
      return false;
    }
  },
  watch: {
    menu (val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
  },
  mounted: function () {
    //get scroll target
    this.container = document.getElementById ( "scroll-target" ),
    this.getCommunities()
  },
  methods: {
    maxDate() {
      let maxDate = new Date();
      maxDate.setFullYear (maxDate.getFullYear() - this.ageMin);
      return maxDate.toISOString().substr(0, 10);
    },
    selectedGeo(address) {
      this.form.homeAddress = address;
    },
    save (date) {
      this.$refs.menu.save(date)
    },
    validate: function (e) {
      this.loading = true;
      axios.post(this.$t('urlSignUp'),
        {
          email:this.form.email,
          telephone:this.form.telephone,
          password:this.form.password,
          givenName:this.form.givenName,
          familyName:this.form.familyName,
          gender:this.form.gender,
          birthDay:this.form.date,
          address:this.form.homeAddress,
          idFacebook:this.form.idFacebook,
          community:this.selectedCommunity
        },{
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res=>{
          window.location.href = this.$t('urlRedirectAfterSignUp',{"email":this.form.email});
          this.errorUpdate = res.data.state;
          this.textSnackbar = (this.errorUpdate) ? this.$t("snackbar.joinCommunity.textError") : this.textSnackOk;
          this.snackbar = true;
      
          console.error(res);
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
    fillForm(data){
      this.form.email = data.email;
      this.form.givenName = data.first_name;
      this.form.familyName = data.last_name;
      this.form.idFacebook = data.id;

    },
    checkAdult (value) {
      var d1 = new Date();
      var d2 = new Date(value);

      var diff =(d1.getTime() - d2.getTime()) / 1000;
      diff /= (60 * 60 * 24);

      //var diffYears =  Math.abs(Math.floor(diff/365.24) ) ;
    },
    checkEmail(){
      this.loadingCheckEmailAldreadyTaken = true;
      axios.post(this.$t('checkEmail.url'),
        {
          email:this.form.email
        },{
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(response=>{
          if(!response.data.error){
            if(response.data.message!==""){
              this.emailAlreadyTaken = true;
            }
            else{
              this.emailAlreadyTaken = false;
            }
          }
        })
        .catch(function (error) {
          console.error(error);
        })
        .finally(()=>{
          this.loadingCheckEmailAldreadyTaken = false;
        });    
    },
    nextStep (n) {
      this.step += 1
    },
    previousStep (n) {
      this.step -= 1
    },
    emitEvent: function() {
      this.$emit("change", {
        communities: this.selectedCommunity
      });
    },

    // remove selected community
    toggleSelected(){
      this.selectedCommunity = !this.selectedCommunity;
    },
    
    // should be get all communities
    getCommunities() {
      axios.post(this.$t("communities.route"))
        .then(res => {
          this.communities = res.data; 
        });
    }
  }

};
</script>
<style lang="scss" scoped>
.v-stepper{
  box-shadow:none;
  .v-stepper__step{
      padding-top:5px;
      padding-bottom:5px;
    .v-stepper__label{
      span{
        text-shadow:none !important;
      }
    }
  }
}
</style>
