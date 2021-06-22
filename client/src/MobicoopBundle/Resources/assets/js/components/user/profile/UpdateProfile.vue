/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

<template>
  <v-container>
    <!--SnackBar-->
    <v-snackbar
      v-model="snackbar"
      :color="(this.errorUpdate)?'error':'success'"
      top
    >
      {{ (this.errorUpdate)?this.textSnackError:this.textSnackOk }}
      <v-btn
        color="white"
        text
        @click="snackbar = false"
      >
        <v-icon>mdi-close-circle-outline</v-icon>
      </v-btn>
    </v-snackbar>
    <v-row
      justify-center
      text-center
    >
      <v-col class="text-center">
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
        >
          <!--Upload Avatar-->
          <v-row justify="center">
            <v-col >
              <v-avatar
                color="lighten-3"
                size="225px"
              >
                <img
                  :src="urlAvatar"
                  alt="avatar"
                  id="avatar"
                >
              </v-avatar>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="3"  xl="3"
              sm="8" justify-self="center" align-self="center" v-if="!displayFileUpload">

            <v-btn
              :loading="loadingDelete"
              color="warning"
              class="ma-2 white--text pa-2 pr-3"
              rounded
              @click="avatarDelete"
            >
              {{ $t('avatar.delete.label') }}
              <v-icon right dark>mdi-delete</v-icon>
            </v-btn>


            </v-col>

            <v-col cols="5" class="text-center" justify-self="center" align-self="center" v-else>
              <v-file-input
                v-model="avatar"
                :rules="avatarRules"
                accept="image/png, image/jpeg, image/jpg, image/bmp"
                :label="$t('avatar.label')"
                prepend-icon="mdi-image"
                :change="previewAvatar()"
              />
            </v-col>
          </v-row>
          <v-row class="text-left">
            <v-col cols="6"><v-alert type="success">{{ $t('savedCo2',{savedCo2:savedCo2}) }} CO<sup>2</sup></v-alert></v-col>
          </v-row>
          <v-row class="text-left title font-weight-bold">
            <v-col>{{ $t('titles.personnalInfos') }}</v-col>
          </v-row>

         
          <!--Email-->
          <v-row 
            justify="start" 
          >
            <v-col cols="9" md="6" sm="4">
                  <v-text-field
                    v-model="email"
                    :label="$t('email.label')"
                    type="email"
                    class="email"
                   
                  />
            </v-col>
          <!-- email verified -->
            <v-col cols="1" v-if="email && emailVerified == true" >
              <v-tooltip 
                color="info" 
                top
              >
                <template v-slot:activator="{ on }">
                  <v-icon 
                    color="success" 
                    v-on="on"  
                    class="mt-7 ml-n12"
                  >
                    mdi-check-circle-outline
                  </v-icon>
                </template>
                  <span> {{$t('email.tooltips.verified')}}</span>
              </v-tooltip>
            </v-col>
          <!--email verification -->
            <v-col cols="1"  v-if="emailVerified == false">
              <v-tooltip 
                color="info" 
                top
              >
                <template 
                  v-slot:activator="{ on }"
                >
                  <v-icon 
                    color="warning" 
                    v-on="on" 
                    class="mt-7 ml-n12" 
                  >
                    mdi-alert-circle-outline
                  </v-icon>
                </template>
                  <span>{{$t('email.tooltips.notVerified')}}</span>
              </v-tooltip>
            </v-col>
            <v-col  v-if="emailVerified == false" align="start">
              <v-btn 
                rounded color="secondary" 
                @click="sendValidationEmail" 
                class="mt-4" 
                :loading="loadingEmail"
              >
                {{ !emailSended ? $t('email.buttons.label.generateEmail') : $t('email.buttons.label.generateEmailAgain')}}
              </v-btn>
            </v-col>
          </v-row>

          <!--Telephone-->
          <v-row 
            justify="start" 
          >
            <v-col cols="9" md="6" sm="4">
              <v-text-field
                v-model="telephone"
                :label="$t('phone.label')"
                class="telephone"
                :rules="telephoneRules"
              />
            </v-col>
          <!-- phone number verified -->
            <v-col cols="1" v-if="telephone && phoneVerified == true" >
              <v-tooltip 
                color="info" 
                top
              >
                <template v-slot:activator="{ on }">
                  <v-icon 
                    color="success" 
                    v-on="on"  
                    class="mt-7 ml-n12"
                  >
                    mdi-check-circle-outline
                  </v-icon>
                </template>
                  <span> {{$t('phone.tooltips.verified')}}</span>
              </v-tooltip>
            </v-col>
          <!-- phone number verification -->
            <v-col cols="1"  v-if="diplayVerification && telephone && phoneVerified == false">
              <v-tooltip 
                color="info" 
                top
              >
                <template 
                  v-slot:activator="{ on }"
                >
                  <v-icon 
                    color="warning" 
                    v-on="on" 
                    class="mt-7 ml-n12" 
                  >
                    mdi-alert-circle-outline
                  </v-icon>
                </template>
                  <span>{{$t('phone.tooltips.notVerified')}}</span>
              </v-tooltip>
            </v-col>
            <v-col  v-if="diplayVerification && telephone && phoneVerified == false" align="start">
              <v-btn 
                rounded color="secondary" 
                @click="generateToken" class="mt-4" 
                :loading="loadingToken"
              >
                {{phoneToken == null ? $t('phone.buttons.label.generateToken') : $t('phone.buttons.label.generateNewToken')}}
              </v-btn>
            </v-col>
            <v-col 
             cols="9" md="6" sm="4"
              v-if="phoneToken != null && telephone && phoneVerified == false"
            >
              <v-text-field
                v-model="token"
                :rules="tokenRules"
                class="mt-2"
                :label="$t('phone.validation.label')"
                  />
            </v-col>
            <v-col cols="1"/>
            <v-col 
              class="justify-center" 
              v-if="phoneToken != null && telephone && phoneVerified == false "
              align="start"
            >
              <v-btn 
                rounded 
                color="secondary" 
                @click="validateToken" 
                class="mt-4"
                :loading="loadingValidatePhone"
              >
                {{$t('phone.buttons.label.validate')}}
              </v-btn>
            </v-col>
          </v-row>

          <!-- Phone display preferences -->
          <v-radio-group
            :label="$t('phoneDisplay.label.general')"
            v-model="phoneDisplay['value']"
          >
            <v-radio
              color="secondary"
              v-for="(phoneDisplay, index) in phoneDisplays"
              :key="index"
              :label="phoneDisplay.label"
              :value="phoneDisplay.value"
            >
            </v-radio>
          </v-radio-group>

          <!--GivenName-->
          <v-text-field
            v-model="givenName"
            :label="$t('givenName.label')"
            class="givenName"
          />

          <!--FamilyName-->
          <v-text-field
            v-model="familyName"
            :label="$t('familyName.label')"
            class="familyName"
          />

          <!--Gender-->
          <v-select
            v-model="gender"
            :label="$t('gender.label')"
            :items="genders"
            item-text="gender"
            item-value="value"
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
              :value ="computedBirthdateFormat"
              :label="$t('birthDay.label')"
              :rules="[ birthdayRules.checkIfAdult, birthdayRules.required ]"
              readonly
              v-on="on"
              />
            </template>
            <v-date-picker
              ref="picker"
              v-model ="birthDay"
              :max="maxDate()"
              :locale="locale"
              first-day-of-week="1"
              @change="save"
            />
          </v-menu>

          <!--NewsSubscription-->
          <v-row>
            <v-col>
              <v-switch
                v-model="newsSubscription"
                :label="$t('news.label', {platform:platform})"
                inset
                color="secondary"
                @change="dialog = !newsSubscription"
              >
                <v-tooltip
                  right
                  slot="append"
                  color="info"
                  :max-width="'35%'"
                >
                  <template v-slot:activator="{ on }">
                    <v-icon justify="left" v-on="on">
                      mdi-help-circle-outline
                    </v-icon>
                  </template>
                  <span>{{ $t('news.tooltip') }}</span>
                </v-tooltip>
              </v-switch>
            </v-col>
          </v-row>

          <!--Confirmation Popup-->
          <v-dialog v-model="dialog" persistent max-width="500">
            <v-card>
              <v-card-title class="text-h5">{{ $t('news.confirmation.title') }}</v-card-title>
              <v-card-text v-html="$t('news.confirmation.content')"></v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary darken-1" text @click="dialog=false; newsSubscription=true">{{ $t('no') }}</v-btn>
                <v-btn color="secondary darken-1" text @click="dialog=false">{{ $t('yes') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <!--Save Button-->
          <v-dialog
            v-model="dialogEmail"
            persistent
            max-width="450"
          >
            <template v-slot:activator="{ on, attrs }">
              <v-btn
                class="button saveButton"
                color="secondary"
                rounded
                :disabled="!valid"
                :loading="loading"
                type="button"
                :value="$t('save')"
                @click="update"
              >
                {{ $t('save') }}
              </v-btn>
          </template>
          <v-card>
            <v-card-title class="headline">
              {{$t('dialogEmail.title')}}
            </v-card-title>
            <v-card-text v-html="$t('dialogEmail.content')"></v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                color="error"
                text
                @click="cancel"
              >
               {{$t('dialogEmail.buttons.cancelUpdate')}}
              </v-btn>
              <v-btn
                color="primary darken-1"
                text
                @click="validate"
              >
                 {{$t('dialogEmail.buttons.confirmUpdate')}}
              </v-btn>
            </v-card-actions>
            </v-card>
          </v-dialog>
        </v-form>
      </v-col>
    </v-row>
    <v-row class="justify-center">
      <v-col cols="12"
 >
        <!--GeoComplete-->
        <GeoComplete
          :url="geoSearchUrl"
          :label="$t('homeTown.label')"
          :token="user ? user.token : ''"
          :init-address="homeAddress"
          :display-name-in-selected="false"
          @address-selected="homeAddressSelected"
        />
      </v-col>
      <v-col 
        cols="3" 
        align="center"
      >
        <v-btn 
          rounded 
          color='secondary' 
          class="mt-4" 
          :disabled='disabledAddress' 
          :loading='loadingAddress' 
          type="button"
          @click='updateAddress'
        >
          {{$t('address.update.label')}}
        </v-btn>
      </v-col>
    </v-row>
    <v-row class="text-left title font-weight-bold">
      <v-col>{{ $t('titles.password') }}</v-col>
    </v-row>
    <v-row>
      <ChangePassword />
    </v-row>
    <!-- Delete account -->
    <v-row class="text-left title font-weight-bold">
      <v-col>{{ $t('titles.deleteAccount') }}</v-col>
    </v-row>

    <v-row>

      <v-container>
        <v-col class="text-center">
      <v-dialog
              v-model="dialogDelete"
              width="500"
      >
        <template v-slot:activator="{ on }" >
          <!--Delete button -->
          <v-btn
                  class="button"
                  v-on="on"
                  color="error"
                  rounded
                  :disabled="!valid || disabledCreatedEvents || disabledOwnedCommunities"
                  :loading="loading"
                  type="button"
                  :value="$t('save')"
          >
            {{ $t('buttons.supprimerAccount') }}
          </v-btn>
        </template>

        <v-card>
          <v-card-title
                  v-if="hasCreatedEvents || hasOwnedCommunities"
                  class="text-h5 error--text"
                  primary-title
          >
            {{ $t('dialog.titles.deletionImpossible') }}
          </v-card-title>
          <v-card-title
                  v-else
                  class="text-h5"
                  primary-title
          >
            {{ $t('dialog.titles.deleteAccount') }}
          </v-card-title>

          <v-card-text>
            <p v-if="hasOwnedCommunities" v-html="$t('dialog.content.errorCommunities')"></p>
            <p v-else-if="hasCreatedEvents" v-html="$t('dialog.content.errorEvents')"></p>
            <p v-else v-html="$t('dialog.content.deleteAccount')"></p>
          </v-card-text>

          <v-divider></v-divider>
          <v-card-actions v-if="hasCreatedEvents || hasOwnedCommunities">
            <v-spacer></v-spacer>
            <v-btn
              color="primary darken-1"
              text
              @click="dialogDelete = false; newsSubscription = true"
            >
              {{ $t('dialog.buttons.close') }}
            </v-btn>
          </v-card-actions>
          <v-card-actions v-else>
            <v-spacer></v-spacer>
            <v-btn
              color="primary darken-1"
              text
              @click="dialogDelete = false; newsSubscription = true"
            >
              {{ $t('no') }}
            </v-btn>
            <v-btn
              color="primary"
              text
              :href="$t('route.supprimer')"
              @click="dialogDelete = false"
            >
              {{ $t('dialog.buttons.confirmDelete') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
        </v-col>
      </v-container>
    </v-row>
  </v-container>
</template>

<script>
import maxios from "@utils/maxios";
import moment from "moment";
import GeoComplete from "@js/components/utilities/GeoComplete";
import ChangePassword from "@components/user/profile/ChangePassword";
import { merge } from "lodash";
import {messages_en, messages_fr, messages_eu, messages_nl} from "@translations/components/user/profile/UpdateProfile/";
import {messages_client_en, messages_client_fr, messages_client_eu, messages_client_nl} from "@clientTranslations/components/user/profile/UpdateProfile/";

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
    GeoComplete,
    ChangePassword
  },
  props: {
    avatarSize: {
      type: String,
      default: null
    },
    geoSearchUrl: {
      type: String,
      default: null
    },
    user: {
      type: Object,
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
    platform: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      dialog: false,
      dialogDelete: false,
      snackbar: false,
      textSnackOk: this.$t('snackBar.profileUpdated'),
      textSnackError: this.$t("snackBar.passwordUpdateError"),
      errorUpdate: false,
      valid: true,
      errors: [],
      loading: false,
      loadingDelete: false,
      gender: this.user.gender,
      email: this.user.email,
      telephone: this.user.telephone,
      givenName: this.user.givenName,
      familyName: this.user.familyName,
      birthDay: this.user.birthDate ? this.user.birthDate.date : null,
      homeAddress: this.user.homeAddress ? this.user.homeAddress : null,
      phoneToken: this.user.phoneToken,
      phoneValidatedDate: this.user.phoneValidatedDate,
      emailValidatedDate: this.user.validatedDate,
      token: null,
      menu : false,
      genders:[
        { value: 1, gender: this.$t('gender.values.female')},
        { value: 2, gender: this.$t('gender.values.male')},
        { value: 3, gender: this.$t('gender.values.other')},
      ],
      phoneDisplay: {
        value: this.user.phoneDisplay
      },
      phoneDisplays:[
        { value: 1, label: this.$t('phoneDisplay.label.restricted')},
        { value: 2, label: this.$t('phoneDisplay.label.all')}
      ],
      avatar: null,
      avatarRules: [
        v => !v || v.size < this.avatarSize || this.$t("avatar.size")+" (Max "+(this.avatarSize/1000000)+"MB)"
      ],
      tokenRules: [
         v => (/^\d{4}$/).test(v) || this.$t("phone.token.inputError")
      ],
      telephoneRules: [
          v => (/^((\+)33|0)[1-9](\d{2}){4}$/).test(v) || this.$t("phone.errors.valid")
      ],
      birthdayRules : {
        required:  v => !!v || this.$t("birthDay.errors.required"),
        checkIfAdult : value =>{
          var d1 = new Date();
          var d2 = new Date(value);

          var diff =(d1.getTime() - d2.getTime()) / 1000;
          diff /= (60 * 60 * 24);

          var diffYears =  Math.abs(Math.floor(diff/365.24) ) ;
          return diffYears >= 16 || this.$t("birthDay.errors.notadult")
        }
      },
      newsSubscription: this.user && this.user.newsSubscription !== null ? this.user.newsSubscription : null,
      urlAvatar: this.user.avatars[this.user.avatars.length-1],
      displayFileUpload: (this.user.images[0]) ? false : true,
      phoneVerified: null,
      emailVerified: false,
      emailSended: false,
      loadingEmail: false,
      diplayVerification: this.user.telephone ? true : false,
      loadingToken: false,
      loadingValidatePhone: false,
      disabledAddress: true,
      loadingAddress: false,
      ownedCommunities: null,
      createdEvents: null,
      hasCreatedEvents: false,
      hasOwnedCommunities: false,
      disabledOwnedCommunities: false,
      disabledCreatedEvents: false,
      locale: localStorage.getItem("X-LOCALE"),
      emailChanged: false,
      dialogEmail: false


    };
  },
   watch: {
    menu (val) {
      val && setTimeout(() => (this.$refs.picker.activePicker = 'YEAR'))
    },
    telephone (val) {
      this.phoneToken = null;
      this.diplayVerification = false;
    }, 
    email (val) {
      this.emailChanged = true;
    }
   },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },
    computedBirthdateFormat () {
       if (this.birthDay) {
        return moment.utc(this.birthDay).format("YYYY-MM-DD");
      }
      return null;
    },
    savedCo2(){
      return Number.parseFloat(this.user.savedCo2  / 1000000 ).toPrecision(1);
    }
  },
  mounted() {
    this.checkVerifiedPhone();
    this.checkVerifiedEmail();
    this.getOwnedCommunities();
    this.getCreatedEvents();
  },
  methods: {
    homeAddressSelected(address){
      this.homeAddress = address;
      this.disabledAddress = false;
    },
    save (date) {
      this.$refs.menu.save(date)
    },
   
    validate () {
      if (this.$refs.form.validate()) {
        this.checkForm();
        this.dialogEmail = false;
      }
    },
    update() {
      if (this.emailChanged) {
        this.dialogEmail = true;
      } else {
        this.validate();
      }
    },
    cancel () {
      window.location.reload();
    },
    checkForm () {
      this.loading = true;
      let updateUser = new FormData();
      updateUser.append("email", this.email);
      updateUser.append("familyName", this.familyName);
      updateUser.append("gender", this.gender);
      updateUser.append("givenName", this.givenName);
      updateUser.append("telephone", this.telephone);
      updateUser.append("birthDay", this.birthDay);
      updateUser.append("avatar", this.avatar);
      updateUser.append("newsSubscription", this.newsSubscription);
      updateUser.append("phoneDisplay", this.phoneDisplay.value);

      maxios
        .post(this.$t('route.update'), updateUser,
          {
            headers:{
              'content-type': 'multipart/form-data'
            }
          })
        .then(res => {
          this.errorUpdate = res.data.state;
          this.snackbar = true;
          this.loading = false;
          if (this.user.telephone != this.telephone) {
            this.phoneValidatedDate = null;
            this.phoneToken = null;
            this.diplayVerification = true;
            this.checkVerifiedPhone();
            this.checkVerifiedEmail();
          }
          //this.urlAvatar = res.data.versions.square_800;
          this.displayFileUpload = false; 
        })
        .catch(error => {
          window.location.reload();
      });
    },
    updateAddress () {
      this.loadingAddress = true;
      this.homeAddress.id = this.user.homeAddress ? this.user.homeAddress.id : null;
      maxios
        .post(this.$t('address.update.route'), this.homeAddress,
          {
            headers:{
              'content-type': 'application/json'
            }
          })
        .then(res => {
          this.homeAddress = res.data;
          this.loadingAddress = false;
          this.disabledAddress = true;
        });
    },
    avatarDelete () {
      this.loadingDelete = true;
      maxios
        .get(this.$t('avatar.delete.route'))
        .then(res => {
          this.errorUpdate = res.data.state;
          this.displayFileUpload = true;
          this.loadingDelete = false;
          this.urlAvatar = res.data[res.data.length-1];
        });
    },
    previewAvatar() {
      if(this.avatar) {
        let reader  = new FileReader();
        reader.addEventListener("load", function () {
          this.urlAvatar = reader.result; // UPDATE PREVIEW
        }.bind(this), false);
        reader.readAsDataURL(this.avatar); // FIRE LOAD EVENT
      } 
      // else {
      //   this.urlAvatar = this.user.avatars[this.user.avatars.length-1]; // RESET AVATAR
      // }
    },
    checkVerifiedPhone() {
      if (this.telephone !== null) {
        this.phoneVerified = this.phoneValidatedDate ? true : false;
      }
    },
    checkVerifiedEmail() {
      if (this.email !== null) {
        this.emailVerified = this.emailValidatedDate ? true : false;
      }
    },
    generateToken() {
    this.loadingToken = true;   
    axios 
      .get(this.$t('phone.token.route'))
      .then(res => {
          if (res.data.state) {
            this.errorUpdate = true;
            this.textSnackError = this.$t('snackBar.tokenError');
            this.snackbar = true;
          }
          this.textSnackOk = this.$t('snackBar.tokenOk');
          this.snackbar = true;
          this.phoneToken = true;
          this.token = null;
          this.loadingToken = false;
        })
    },
    sendValidationEmail() {
    this.loadingEmail = true;   
    axios 
      .get(this.$t('email.verificationRoute'))
      .then(res => {
          if (res.data.state) {
            this.errorUpdate = true;
            this.textSnackError = this.$t('snackBar.emailError');
            this.snackbar = true;
          }
          this.textSnackOk = this.$t('snackBar.emailOk');
          this.snackbar = true;
          this.emailSended = true;
          this.loadingEmail = false;   
        })
    },
    validateToken() {
      this.loadingValidatePhone = true; 
      maxios
        .post(this.$t('phone.validation.route'),
        {
          token: this.token,
          telephone: this.telephone
        },{
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          if(!res.data){
            this.errorUpdate = true;
            this.textSnackError = this.$t("snackBar.unknown");
            this.snackbar = true;
          }
          else{
            this.errorUpdate = false;
            this.textSnackOk = this.$t("snackBar.phoneValidated");
            this.snackbar = true;
             this.phoneVerified = true;
          }
          this.loadingValidatePhone = false;
        })
        // Todo create "emit" event to refresh the alerts
    },
    getOwnedCommunities() {
      let params = {
        'userId':this.user.id
      }
      this.disabledOwnedCommunities = true;
      maxios.post(this.$t("communities.route"), params)
        .then(res => {
          if (res.data.length > 0) {
            this.ownedCommunities = res.data;
            this.hasOwnedCommunities = true;
          }
          this.disabledOwnedCommunities = false;
        });
    },
    getCreatedEvents() {
      let params = {
        'userId':this.user.id
      }
      this.disabledCreatedEvents = true;
      maxios.post(this.$t("events.route"), params)
        .then(res => {
          if (res.data.length > 0) {
            this.createdEvents = res.data;
             this.createdEvents.forEach(createdEvent => {
               if (moment(createdEvent.toDate.date) >= moment(new Date())) {
                 this.hasCreatedEvents = true;
               }
             });
          }
          this.disabledCreatedEvents = false;
        });
    },
    maxDate() {
      let maxDate = new Date();
      maxDate.setFullYear (maxDate.getFullYear() - this.ageMin);
      return maxDate.toISOString().substr(0, 10);
    }
  }
}
</script>
<style lang="scss" scoped>
  #avatar{
    width:auto !important;
  }
</style>
