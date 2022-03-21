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
      v-if="selectedCommunity"
      v-model="snackbar"
      :color="
        errorUpdate
          ? 'error'
          : communities && communities.validationType == 1
            ? 'warning'
            : 'success'
      "
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
      <v-row justify="center">
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="center"
        >
          <h1>{{ $t("title") }}</h1>
        </v-col>
      </v-row>
      <v-row
        v-if="!consent"
        justify="center"
      >
        <v-col
          cols="12"
          md="8"
          xl="6"
          align="left"
        >
          <v-alert

            class="warning white--text"
          >
            <v-icon class="white--text">
              mdi-information-outline
            </v-icon> {{ $t('rgpd.consent') }}
          </v-alert>
        </v-col>
      </v-row>
      <v-row
        v-if="consent"
        justify="center"
        class="text-center"
      >
        <v-col
          class="col-4"
        >
          <SsoLogins
            v-if="ssoButtonDisplay"
            class="justify-self-center"
          />
        </v-col>
      </v-row>
      <v-row
        v-if="signupRgpdInfos"
      >
        <v-col
          cols="12"
          align="center"
        >
          <p>
            {{ $t('rgpd.infos') }}
          </p>
          <a
            class="primary--text"
            target="_blank"
            :href="$t('rgpd.link.route')"
          >{{ $t('rgpd.link.label') }}
          </a>
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
            <v-stepper-header class="elevation-0">
              <!--STEP 1 User identification-->
              <v-stepper-step
                :step="1"
                :complete="step1Valid()"
              />
              <v-divider />

              <!--STEP 2 Name - Gender - BirthDate-->
              <v-stepper-step
                :step="2"
                :complete="step2Valid()"
              />
              <v-divider />

              <!--STEP 3 hometown - Community - checkbox-->
              <v-stepper-step
                :step="3"
              />
            </v-stepper-header>

            <!--STEP 1 User identification-->
            <v-stepper-content step="1">
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
                  :label="$t('email.placeholder') + ` *`"
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
                  {{ textEmailError }}
                </v-alert>

                <v-text-field
                  id="telephone"
                  v-model="form.telephone"
                  :label="$t('phone.placeholder') + ` *`"
                  required
                  :rules="form.telephoneRules"
                  name="telephone"
                  @keypress="isNumber(event)"
                  @focusin="phoneNumberValid = true"
                  @focusout="checkPhoneNumberValidity"
                />
                <v-alert
                  v-if="!phoneNumberValid"
                  type="error"
                >
                  {{ $t('checkPhoneValidity.error') }}
                </v-alert>
                <v-text-field
                  id="password"
                  v-model="form.password"
                  :append-icon="form.showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  :rules="[
                    form.passWordRules.required,
                    form.passWordRules.min,
                    form.passWordRules.checkUpper,
                    form.passWordRules.checkLower,
                    form.passWordRules.checkNumber,
                  ]"
                  :type="form.showPassword ? 'text' : 'password'"
                  name="password"
                  :label="$t('password.placeholder') + ` *`"
                  required
                  @click:append="form.showPassword = !form.showPassword"
                />
                <v-btn
                  id="buttonNext1"
                  ref="button"
                  rounded
                  class="my-13"
                  color="secondary"
                  type="submit"
                  :disabled="!step1 || !consent"
                  @click="nextStep(1)"
                >
                  {{ $t("button.next") }}
                </v-btn>
                <v-card-text
                  v-if="loginLinkInConnection"
                >
                  <a
                    :href="$t('urlLogin')"
                    class="font-italic"
                  >
                    {{ $t('login') }}
                  </a>
                </v-card-text>
              </v-form>
            </v-stepper-content>

            <!--STEP 2 Name - Gender - BirthDate-->
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
                  id="givenName"
                  v-model="form.givenName"
                  :rules="form.givenNameRules"
                  :label="$t('givenName.placeholder') + ` *`"
                  class="givenName"
                  required
                />
                <v-text-field
                  id="familyName"
                  v-model="form.familyName"
                  :rules="form.familyNameRules"
                  :label="$t('familyName.placeholder') + ` *`"
                  class="familyName"
                  required
                />
                <v-select
                  v-model="form.gender"
                  :items="form.genderItems"
                  item-text="genderItem"
                  item-value="genderValue"
                  :rules="form.genderRules"
                  :label="$t('gender.label') + ` *`"
                  required
                />
                <v-menu
                  v-if="birthDateDisplay"
                  ref="menu"
                  v-model="menu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="290px"
                >
                  <template v-slot:activator="{ on }">
                    <v-text-field
                      id="birthday"
                      v-model="form.date"
                      :label="$t('birthDate.placeholder')+` *`"
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
                    {{ $t("button.previous") }}
                  </v-btn>
                  <v-btn
                    id="buttonNext2"
                    ref="button"
                    rounded
                    class="my-13"
                    color="secondary"
                    type="submit"
                    :disabled="!step2 || !consent"
                    @click="nextStep(2)"
                  >
                    {{ $t("button.next") }}
                  </v-btn>
                </v-row>
              </v-form>
            </v-stepper-content>

            <!--STEP 3 hometown - community - ckeckbox-->
            <v-stepper-content :step="3">
              <v-form
                id="step3"
                ref="form"
                v-model="step3"
                class="pb-2"
                @submit.prevent
              >
                <!-- hometown -->
                <geocomplete
                  :uri="geoSearchUrl"
                  chip
                  :restrict="['locality']"
                  :label="$t('homeTown.placeholder')"
                  :required="requiredHomeAddress"
                  @address-selected="selectedGeo"
                />
                <!-- community -->
                <v-row
                  v-if="communityShow"
                  class="text-justify pb-5"
                >
                  <community-help
                    :display-title-community="false"
                  />
                </v-row>

                <v-autocomplete
                  v-if="communityShow"
                  v-model="selectedCommunity"
                  :items="communities"
                  outlined
                  chips
                  :loading="loadingCommunity"
                  :disabled="loadingCommunity"
                  :label="requiredCommunity ? $t('communities.label')+` *` : $t('communities.label')"
                  item-text="name"
                  item-value="id"
                  :required="requiredCommunity"
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

                <!-- checkbox -->
                <v-checkbox
                  v-if="!birthDateDisplay"
                  class="check mt-12"
                  color="primary"
                  :rules="form.checkboxLegalAgeRules"
                  required
                >
                  <template v-slot:label>
                    <div>
                      {{ $t("legalAge.text") }}
                    </div>
                  </template>
                </v-checkbox>


                <!-- checkbox -->
                <v-checkbox
                  v-model="form.validation"
                  color="primary"
                  :rules="form.checkboxRules"
                  required
                >
                  <template v-slot:label>
                    <div>
                      {{ $t("chart.text") }}
                      <a
                        class="primary--text"
                        target="_blank"
                        :href="$t('chart.route')"
                        @click.stop
                      >{{ $t("chart.link") }}
                      </a>
                    </div>
                  </template>
                </v-checkbox>

                <!-- checkbox -->
                <v-checkbox
                  v-model="form.newsSubscription"
                  class="check"
                  color="primary"
                  required
                >
                  <template v-slot:label>
                    <div>
                      {{ $t("newsSubscription.text") }}
                    </div>
                  </template>
                </v-checkbox>

                <v-row
                  v-if="emailAlreadyTaken || !phoneNumberValid"
                  justify="center"
                  align="center"
                >
                  <v-alert
                    v-if="emailAlreadyTaken"
                    type="error"
                  >
                    {{ textEmailError }}
                  </v-alert>
                  <v-alert
                    v-if="!phoneNumberValid"
                    type="error"
                  >
                    {{ $t('checkPhoneValidity.error') }}
                  </v-alert>
                </v-row>
                <v-row
                  justify="center"
                  align="center"
                  class="mb-25"
                >
                  <v-btn
                    ref="button"
                    rounded
                    class="my-5 mr-12"
                    color="secondary"
                    @click="--step"
                  >
                    {{ $t("button.previous") }}
                  </v-btn>
                  <v-btn
                    color="secondary"
                    rounded
                    class="my-5"
                    :loading="loading"
                    :disabled="
                      !step3 || !step2 || !step1 || loading || isDisable
                    "
                    @click="validate"
                  >
                    {{ $t("button.register") }}
                  </v-btn>
                </v-row>
              </v-form>
            </v-stepper-content>
          </v-stepper>
        </v-col>
      </v-row>
      <v-row
        v-if="showFacebookSignUp && consentSocial"
        justify="center"
        class="text-center mt-n12"
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
        v-else-if="showFacebookSignUp"
        class="justify-center"
      >
        <v-col class="col-4 text-center">
          <v-alert
            type="info"
            class="text-left"
          >
            {{ $t('rgpd.socialServicesUnavailableWithoutConsent') }}
          </v-alert>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import maxios from "@utils/maxios";
import Geocomplete from "@components/utilities/geography/Geocomplete";
import CommunityHelp from "@components/community/CommunityHelp";

import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/SignUp/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/SignUp/";
import MFacebookAuth from "@components/user/MFacebookAuth";
import SsoLogins from '@components/user/SsoLogins';

let MessagesMergedEn = merge(messages_en, messages_client_en);
let MessagesMergedNl = merge(messages_nl, messages_client_nl);
let MessagesMergedFr = merge(messages_fr, messages_client_fr);
let MessagesMergedEu = merge(messages_eu, messages_client_eu);
export default {
  i18n: {
    messages: {
      'en': MessagesMergedEn,
      'nl': MessagesMergedNl,
      'fr': MessagesMergedFr,
      'eu': MessagesMergedEu
    },
  },
  components: {
    Geocomplete,
    MFacebookAuth,
    CommunityHelp,
    SsoLogins
  },
  props: {
    geoSearchUrl: {
      type: String,
      default: null,
    },
    sentToken: {
      type: String,
      default: null,
    },
    ageMin: {
      type: String,
      default: null,
    },
    ageMax: {
      type: String,
      default: null,
    },
    showFacebookSignUp: {
      type: Boolean,
      default: false,
    },
    facebookLoginAppId: {
      type: String,
      default: null,
    },
    requiredHomeAddress: {
      type: Boolean,
      default: false,
    },
    requiredCommunity: {
      type: Boolean,
      default: false,
    },
    communityShow: {
      type: Boolean,
      default: false,
    },
    id: {
      type: Number,
      default: null,
    },
    type: {
      type: String,
      default: 'default'
    },
    loginLinkInConnection: {
      type: Boolean,
      default: false
    },
    signupRgpdInfos: {
      type: Boolean,
      default: false
    },
    newsSubscriptionDefault: {
      type: Boolean,
      default: false
    },
    birthDateDisplay: {
      type: Boolean,
      default: false
    },
    ssoButtonDisplay:{
      type: Boolean,
      default:false
    }
  },
  data() {
    return {
      step: 1,
      event: null,
      loading: false,
      loadingCommunity: false,

      //snackbar
      snackbar: false,
      errorUpdate: false,
      textSnackbar: null,
      textSnackOk: null,
      textEmailError: null,

      //step validators
      step1: true,
      step2: true,
      step3: true,
      menu: false,

      //scrolling data
      selected: null,
      duration: 1000,
      offset: 180,
      easing: "easeOutQuad",
      container: "scroll-target",

      emailAlreadyTaken: false,
      phoneNumberValid: true,
      loadingCheckEmailAldreadyTaken: false,
      form: {
        createToken: this.sentToken,
        email: null,
        emailRules: [
          (v) => !!v || this.$t("email.errors.required"),
          (v) => /.+@.+/.test(v) || this.$t("email.errors.valid"),
        ],
        givenName: null,
        givenNameRules: [
          (v) => !!v || this.$t("givenName.errors.required"),
        ],
        familyName: null,
        familyNameRules: [
          (v) => !!v || this.$t("familyName.errors.required"),
        ],
        gender: null,
        genderRules: [
          (v) => !!v || this.$t("gender.errors.required"),
        ],
        genderItems: [
          {
            genderItem: this.$t("gender.values.female"),
            genderValue: "1",
          },
          {
            genderItem: this.$t("gender.values.male"),
            genderValue: "2",
          },
          {
            genderItem: this.$t("gender.values.other"),
            genderValue: "3",
          },
        ],
        date: null,
        telephone: null,
        telephoneRules: [
          (v) => !!v || this.$t("phone.errors.required"),
          (v) =>
            this.phoneNumberValid ||
            this.$t("phone.errors.valid"),
        ],
        password: null,
        showPassword: false,
        passWordRules: {
          required: (v) =>
            !!v || this.$t("password.errors.required"),
          min: (v) =>
            (v && v.length >= 8) || this.$t("password.errors.min"),
          checkUpper: (value) => {
            const pattern = /^(?=.*[A-Z]).*$/;
            return (
              pattern.test(value) ||
              this.$t("password.errors.upper")
            );
          },
          checkLower: (value) => {
            const pattern = /^(?=.*[a-z]).*$/;
            return (
              pattern.test(value) ||
              this.$t("password.errors.lower")
            );
          },
          checkNumber: (value) => {
            const pattern = /^(?=.*[0-9]).*$/;
            return (
              pattern.test(value) ||
              this.$t("password.errors.number")
            );
          },
        },
        birthdayRules: {
          required: (v) =>
            !!v || this.$t("birthDay.errors.required"),
          checkIfAdult: (value) => {
            var d1 = new Date();
            var d2 = new Date(value);

            var diff = (d1.getTime() - d2.getTime()) / 1000;
            diff /= 60 * 60 * 24;

            var diffYears = Math.abs(Math.floor(diff / 365.24));
            return (
              diffYears >= 16 || this.$t("birthDay.errors.notadult")
            );
          },
        },
        homeAddress: null,
        checkboxRules: [
          (v) => !!v || this.$t("chart.required"),
        ],
        checkboxLegalAgeRules: [
          (v) => !!v || this.$t("legalAge.required"),
        ],
        idFacebook: null,
        newsSubscription: this.newsSubscriptionDefault
      },
      communities: [],
      selectedCommunity: null,
      locale: localStorage.getItem("X-LOCALE")
    };
  },
  computed: {
    action() {
      if (this.id === null) return this.$t("urlSignUp");
      switch (this.type) {
      case 'proposal':
        return this.$t("urlSignUpResult", { id: this.id });
      case 'event':
        return this.$t("urlSignUpEvent", { id: this.id });
      default:
        return this.$t("urlSignUp");
      }
    },
    years() {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from(
        { length: ageMax - ageMin },
        (value, index) => currentYear - ageMin - index
      );
    },
    //options of v-scroll
    options() {
      return {
        duration: this.duration,
        offset: this.offset,
        easing: this.easing,
        container: this.container,
      };
    },
    // disable validation if homeAddress and community is empty and required or email already taken
    isDisable() {
      if (this.requiredHomeAddress && !this.form.homeAddress) {
        return true;
      }
      if (this.emailAlreadyTaken || !this.phoneNumberValid) {
        return true;
      }
      if (this.requiredCommunity && !this.selectedCommunity) {
        return true;
      }
      return false;
    },
    consent(){
      return this.$store.getters['up/connectionActive'];
    },
    consentSocial(){
      let social = this.$store.getters['up/social'];
      let socialCookies = this.$store.getters['up/socialCookies'];

      if(social){
        if(socialCookies.length > 0){
          if(socialCookies.filter((socialItem) => socialItem == 'Facebook').length > 0){
            return true;
          }
          else{
            return false;
          }
        }

        return true;
      }

      return social;
    }
  },
  watch: {
    menu(val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = "YEAR"));
    },
    selectedCommunity() {
      this.communities.forEach((community, index) => {
        if (community.id == this.selectedCommunity) {
          this.textSnackOk =
            community.validationType == 1
              ? this.$t("snackbar.joinCommunity.textOkManualValidation")
              : this.$t("snackbar.joinCommunity.textOkAutoValidation");
        }
      });
    },
    step() {
      if (this.step == 3 && this.communityShow) {
        this.loadingCommunity = true;
        this.getCommunities();
      }
    }
  },
  mounted: function() {
    //get scroll target
    (this.container = document.getElementById("scroll-target"))
  },
  methods: {
    maxDate() {
      let maxDate = new Date();
      maxDate.setFullYear(maxDate.getFullYear() - this.ageMin);
      return maxDate.toISOString().substr(0, 10);
    },
    selectedGeo(address) {
      this.form.homeAddress = address;
    },
    save(date) {
      this.$refs.menu.save(date);
    },
    validate: function(e) {
      this.loading = true;
      maxios
        .post(
          this.action,
          {
            email: this.form.email,
            telephone: this.form.telephone,
            password: this.form.password,
            givenName: this.form.givenName,
            familyName: this.form.familyName,
            gender: this.form.gender,
            birthDay: this.form.date,
            address: this.form.homeAddress,
            idFacebook: this.form.idFacebook,
            newsSubscription: this.form.newsSubscription,
            community: this.selectedCommunity ? this.selectedCommunity : null,
          },
          {
            headers: {
              "content-type": "application/json",
            },
          }
        )
        .then((res) => {
          this.errorUpdate = res.data.state;
          this.textSnackbar = this.errorUpdate
            ? this.$t("snackbar.joinCommunity.textError")
            : this.textSnackOk;
          this.snackbar = true;
          if (this.id) {
            // an id is provided, we need to login automatically (it will redirect to the results of the proposal, the publish page for an event, the community...)
            const loginForm = document.createElement("form");
            loginForm.method = "post";
            if (this.type === 'proposal') {
              loginForm.action = this.$t("urlRedirectAfterSignUpResult", { id: this.id});
            } else if (this.type === 'event') {
              loginForm.action = this.$t("urlRedirectAfterSignUpEvent", { id: this.id});
            } else if (this.type === 'community') {
              loginForm.action = this.$t("urlRedirectAfterSignUpCommunity", { id: this.id});
            } else if (this.type === 'publish') {
              loginForm.action = this.$t("urlRedirectAfterSignUpPublish");
            }
            const hiddenFieldEmail = document.createElement("input");
            hiddenFieldEmail.name = "email";
            hiddenFieldEmail.value = this.form.email;
            loginForm.appendChild(hiddenFieldEmail);
            const hiddenFieldPassword = document.createElement("input");
            hiddenFieldPassword.name = "password";
            hiddenFieldPassword.value = this.form.password;
            loginForm.appendChild(hiddenFieldPassword);
            document.body.appendChild(loginForm);
            loginForm.submit();
          } else {
            // usual redirect
            var urlRedirect = this.$t("urlRedirectAfterSignUp", {
              email: this.form.email,
            });
            setTimeout(function() {
              window.location.href = urlRedirect;
            }, 2000);
          }
          //console.error(res);
        })
        .catch(function(error) {
          console.log(error);
        });
    },
    isNumber: function(evt) {
      evt = evt ? evt : window.event;
      var charCode = evt.which ? evt.which : evt.keyCode;
      if (-(charCode < 48 || charCode > 57) && charCode !== 43) {
        evt.preventDefault();
      } else {
        return true;
      }
    },
    fillForm(data) {
      this.form.email = data.email;
      this.form.givenName = data.first_name;
      this.form.familyName = data.last_name;
      this.form.idFacebook = data.id;
    },
    checkEmail() {
      if(!this.form.email){
        return
      }
      this.loadingCheckEmailAldreadyTaken = true;
      maxios
        .post(
          this.$t("checkEmail.url"),
          {
            email: this.form.email,
          },
          {
            headers: {
              "content-type": "application/json",
            },
          }
        )
        .then((response) => {
          if (response.data.error) {
            if (response.data.message !== "") {
              this.textEmailError = response.data.message;
              this.emailAlreadyTaken = true;
            } else {
              this.emailAlreadyTaken = false;
            }
          }
        })
        .catch(function(error) {
          console.error(error);
        })
        .finally(() => {
          this.loadingCheckEmailAldreadyTaken = false;
        });
    },
    checkPhoneNumberValidity(){
      if(!this.form.telephone){
        return
      }
      maxios
        .post(
          this.$t("checkPhoneValidity.url"),
          {
            telephone: this.form.telephone,
          },
          {
            headers: {
              "content-type": "application/json",
            },
          }
        )
        .then((response) => {
          if(response.data.valid){
            this.phoneNumberValid = response.data.valid
          }
          else{
            this.phoneNumberValid = false;
          }
        })
        .catch(function(error) {
          console.error(error);
          this.phoneNumberValid = false;
        });
    },
    nextStep(n) {
      this.step += 1;
    },
    previousStep(n) {
      this.step -= 1;
    },
    step1Valid() {
      return this.form.email && this.form.password && this.form.telephone != null
    },
    step2Valid() {
      if (this.birthDateDisplay){
        return this.form.familyName && this.form.givenName && this.form.gender && this.form.date != null
      } else {
        return this.form.familyName && this.form.givenName && this.form.gender != null
      }
    },
    emitEvent: function() {
      this.$emit("change", {
        communities: this.selectedCommunity,
      });
    },

    // remove selected community
    toggleSelected() {
      this.selectedCommunity = null;
    },

    // should be get all communities
    getCommunities() {
      maxios
        .post(
          this.$t("communities.route"),
          {
            email: this.form.email,
          },
          {
            headers: {
              "content-type": "application/json",
            },
          }
        )
        .then((res) => {
          this.communities = res.data;
          this.loadingCommunity = false;
        });
    }
  },
};
</script>
<style lang="scss" scoped>
.v-stepper {
  box-shadow: none;
  .v-stepper__step {
    padding-top: 5px;
    padding-bottom: 5px;
    .v-stepper__label {
      span {
        text-shadow: none !important;
      }
    }
  }
}
</style>
