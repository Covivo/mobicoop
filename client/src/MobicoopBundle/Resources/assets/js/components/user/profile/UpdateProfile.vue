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
            <v-col cols="3">
              <v-avatar
                color="grey lighten-3"
                size="225px"
              >
                <img
                  :src="urlAvatar"
                  alt="avatar"
                >
              </v-avatar>
            </v-col>
          </v-row>

          <v-row justify="center">
            <v-col cols="3" justify-self="center" align-self="center" v-if="!displayFileUpload">

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

            <v-col cols="5" class="text-center" v-else>
              <v-file-input
                v-model="avatar"
                :rules="avatarRules"
                accept="image/png, image/jpeg, image/bmp"
                :label="$t('avatar.label')"
                prepend-icon="mdi-image"
                :change="previewAvatar()"
              />
            </v-col>
          </v-row>
          <v-row class="text-left title font-weight-bold">
            <v-col>{{ $t('titles.personnalInfos') }}</v-col>
          </v-row>

          <!--Email-->
          <v-text-field
            v-model="email"
            :label="$t('models.user.email.label')"
            type="email"
            class="email"
          />

          <!--Telephone-->
          <v-row 
            justify="start" 
            
          >
            <v-col>
              <v-text-field
                v-model="telephone"
                :label="$t('models.user.phone.label')"
                class="telephone"
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
            <v-col cols="3" xl="3" sm="8"  v-if="diplayVerification && telephone && phoneVerified == false">
              <v-btn 
                rounded color="secondary" 
                @click="generateToken" class="mt-4" 
                :loading="loadingToken"
              >
                {{phoneToken == null ? $t('phone.buttons.label.generateToken') : $t('phone.buttons.label.generateNewToken')}}
              </v-btn>
            </v-col>
            <v-col 
              cols="3" 
              xl="3"
              sm="5"
              v-if="phoneToken != null && telephone && phoneVerified == false"
            >
              <v-text-field
                v-model="token"
                :rules="tokenRules"
                class="mt-2"
                :label="$t('phone.validation.label')"
                  />
            </v-col>
            <v-col 
              cols="2" xl="2" sm="6" class="justify-center" 
              v-if="phoneToken != null && telephone && phoneVerified == false"
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
            :label="$t('models.user.givenName.label')"
            class="givenName"
          />

          <!--FamilyName-->
          <v-text-field
            v-model="familyName"
            :label="$t('models.user.familyName.label')"
            class="familyName"
          />

          <!--Gender-->
          <v-select
            v-model="gender"
            :label="$t('models.user.gender.label')"
            :items="genders"
            item-text="gender"
            item-value="value"
          />

          <!--birthyear-->
          <v-select
            id="birthYear"
            v-model="birthYear"
            :items="years"
            :label="$t('models.user.birthYear.label')"
            class="birthYear"
          />

          <!--GeoComplete-->
          <GeoComplete
            :url="geoSearchUrl"
            :label="$t('models.user.homeTown.label')"
            :token="user ? user.geoToken : ''"
            :init-address="user.homeAddress ? user.homeAddress : null"
            :display-name-in-selected="false"
            @address-selected="homeAddressSelected"
          />

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
              <v-card-title class="headline">{{ $t('news.confirmation.title') }}</v-card-title>
              <v-card-text v-html="$t('news.confirmation.content')"></v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary darken-1" text @click="dialog=false; newsSubscription=true">{{ $t('ui.common.no') }}</v-btn>
                <v-btn color="secondary darken-1" text @click="dialog=false">{{ $t('ui.common.yes') }}</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>

          <!--Save Button-->
          <v-btn
            class="button saveButton"
            color="secondary"
            rounded
            :disabled="!valid"
            :loading="loading"
            type="button"
            :value="$t('ui.button.save')"
            @click="validate"
          >
            {{ $t('ui.button.save') }}
          </v-btn>
        </v-form>
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


      <v-dialog
              v-model="dialogDelete"
              width="500"
      >
        <template v-slot:activator="{ on }">
          <!--Delete button -->
          <v-btn
                  class="button"
                  v-on="on"
                  color="secondary"
                  rounded
                  :disabled="!valid"
                  :loading="loading"
                  type="button"
                  :value="$t('ui.button.save')"
          >
            {{ $t('buttons.supprimerAccount') }}
          </v-btn>
        </template>

        <v-card>
          <v-card-title
                  class="headline"
                  primary-title
          >
            {{ $t('dialog.titles.deleteAccount') }}
          </v-card-title>

          <v-card-text>
            <p v-html="$t('dialog.content.deleteAccount')"></p>
          </v-card-text>

          <v-divider></v-divider>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary darken-1" text @click="dialog=false; newsSubscription=true">{{ $t('ui.common.no') }}</v-btn>
            <v-btn
                    color="primary"
                    text
                    :href="$t('route.supprimer')"
                    @click="dialog = false"
            >
              {{ $t('dialog.buttons.confirmDelete') }}
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>


    </v-row>

  </v-container>
</template>

<script>
import axios from "axios";
import GeoComplete from "@js/components/utilities/GeoComplete";
import ChangePassword from "@components/user/profile/ChangePassword";
import { merge } from "lodash";
import Translations from "@translations/components/user/profile/UpdateProfile.json";
import TranslationsClient from "@clientTranslations/components/user/profile/UpdateProfile.json";

let TranslationsMerged = merge(Translations, TranslationsClient);

export default {
  i18n: {
    messages: TranslationsMerged,
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
      birthYear: this.user.birthYear,
      homeAddress: null,
      phoneToken: this.user.phoneToken,
      phoneValidatedDate: this.user.phoneValidatedDate,
      token: null,
      genders:[
        { value: 1, gender: this.$t('models.user.gender.values.female')},
        { value: 2, gender: this.$t('models.user.gender.values.male')},
        { value: 3, gender: this.$t('models.user.gender.values.other')},
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
      newsSubscription: this.user && this.user.newsSubscription !== null ? this.user.newsSubscription : null,
      urlAvatar: this.user.avatars[this.user.avatars.length-1],
      displayFileUpload: (this.user.images[0]) ? false : true,
      phoneVerified: null,
      diplayVerification: this.user.telephone ? true : false,
      loadingToken: false,
      loadingValidatePhone: false
    };
  },
  computed : {
    years () {
      const currentYear = new Date().getFullYear();
      const ageMin = Number(this.ageMin);
      const ageMax = Number(this.ageMax);
      return Array.from({length: ageMax - ageMin}, (value, index) => (currentYear - ageMin) - index)
    },
  },
  mounted() {
    this.checkVerifiedPhone();
  },
  methods: {
      deleteAccount (){
          axios
            .post(this.$t('route.supprimer'))
            .then(res => {

            });
      },
    homeAddressSelected(address){
      this.homeAddress = address;
    },

    validate () {
      if (this.$refs.form.validate()) {
        this.checkForm();
      }
    },
    checkForm () {
      this.loading = true;
      let updateUser = new FormData();
      updateUser.append("email", this.email);
      updateUser.append("familyName", this.familyName);
      updateUser.append("gender", this.gender);
      updateUser.append("givenName", this.givenName);
      updateUser.append("homeAddress", JSON.stringify(this.user.homeAddress));
      updateUser.append("telephone", this.telephone);
      updateUser.append("birthYear", this.birthYear);
      updateUser.append("avatar", this.avatar);
      updateUser.append("newsSubscription", this.newsSubscription);
      updateUser.append("phoneDisplay", this.phoneDisplay.value);

      axios
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
          }
          this.urlAvatar = res.data.versions.square_800;
          this.displayFileUpload = false; 
        });
    },
    avatarDelete () {
      this.loadingDelete = true;
      axios
        .get(this.$t('avatar.delete.route'))
        .then(res => {
          this.errorUpdate = res.data.state;
          this.urlAvatar = res.data[res.data.length-1];
          this.displayFileUpload = true;
          this.loadingDelete = false;
        });
    },
    previewAvatar() {
      if(this.avatar) {
        let reader  = new FileReader();
        reader.addEventListener("load", function () {
          this.urlAvatar = reader.result; // UPDATE PREVIEW
        }.bind(this), false);
        reader.readAsDataURL(this.avatar); // FIRE LOAD EVENT

      } else {
        this.urlAvatar = this.user.avatars[this.user.avatars.length-1]; // RESET AVATAR
      }
    },
    checkVerifiedPhone() {
      if (this.telephone !== null) {
        this.phoneVerified = this.phoneValidatedDate ? true : false;
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
    validateToken() {
      this.loadingValidatePhone = true; 
      axios
        .post(this.$t('phone.validation.route'),
        {
          token: this.token
        },{
          headers:{
            'content-type': 'application/json'
          }
        })
        .then(res => {
          if (res.data.state) {
            this.errorUpdate = true;
            this.textSnackError = this.$t(res.data.message);
            this.snackbar = true;
          }
          this.phoneVerified = !res.data.state ? true : false;
          this.loadingValidatePhone = false; 
        })
        // Todo create "emit" event to refresh the alerts
    }
  }
}
</script>
