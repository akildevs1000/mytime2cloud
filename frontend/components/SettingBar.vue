<template>
  <div>
    <div
      class="settingSidebar showSettingPanel"
      ng-reflect-ng-class="showSettingPanel"
    >
      <div
        class="settingSidebar-body ps-container ps-theme-default ps ps--active-y"
        ng-reflect-ng-style="[object Object]"
        style="position: relative; max-height: 869px;"
      >
        <div class="fade show active">
          <div class="setting-panel-header">
            Setting Panel
          </div>
          <div class="p-15 border-bottom">
            <h6 class="font-medium m-b-10">
              Select Layout
            </h6>
            <div class="flex flex-wrap hiddenradio">
              <div class="flex flex-col">
                <label
                  ><input type="radio" name="value" value="light"/><img
                    src="assets/images/light.png"
                /></label>
                <div class="mt-1 text-md text-center">
                  Light
                </div>
              </div>
              <div class="flex flex-col mt-3">
                <label
                  ><input type="radio" name="value" value="dark"/><img
                    src="assets/images/dark.png"
                /></label>
                <div class="mt-1 text-md text-center">
                  Dark
                </div>
              </div>
            </div>
          </div>
          <div class="rightSetting">
            <h6 class="font-medium m-b-10">
              Sidebar Menu Color
            </h6>
            <mat-button-toggle-group
              role="group"
              class="mat-button-toggle-group mt-2 mat-button-toggle-group-appearance-standard"
              ng-reflect-value="dark"
              aria-disabled="false"
              ><mat-button-toggle
                role="presentation"
                value="light"
                class="mat-button-toggle mat-button-toggle-appearance-standard"
                ng-reflect-value="light"
                id="mat-button-toggle-1"
                ><button
                  type="button"
                  class="mat-button-toggle-button mat-focus-indicator"
                  id="mat-button-toggle-1-button"
                  tabindex="0"
                  aria-pressed="false"
                  name="mat-button-toggle-group-0"
                >
                  <span class="mat-button-toggle-label-content"
                    >Light</span
                  ></button
                ><span class="mat-button-toggle-focus-overlay"></span
                ><span
                  matripple=""
                  class="mat-ripple mat-button-toggle-ripple"
                  ng-reflect-trigger="[object HTMLButtonElement]"
                  ng-reflect-disabled="false"
                ></span></mat-button-toggle
              ><mat-button-toggle
                role="presentation"
                value="dark"
                class="mat-button-toggle mat-button-toggle-appearance-standard mat-button-toggle-checked"
                ng-reflect-value="dark"
                id="mat-button-toggle-2"
                ><button
                  type="button"
                  class="mat-button-toggle-button mat-focus-indicator"
                  id="mat-button-toggle-2-button"
                  tabindex="0"
                  aria-pressed="true"
                  name="mat-button-toggle-group-0"
                >
                  <span class="mat-button-toggle-label-content"
                    >Dark</span
                  ></button
                ><span class="mat-button-toggle-focus-overlay"></span
                ><span
                  matripple=""
                  class="mat-ripple mat-button-toggle-ripple"
                  ng-reflect-trigger="[object HTMLButtonElement]"
                  ng-reflect-disabled="false"
                ></span></mat-button-toggle
            ></mat-button-toggle-group>
          </div>
          <div class="p-15 border-bottom">
            <h6 class="font-medium m-b-10">
              Color Theme
            </h6>
            <div class="theme-setting-options">
              <ul class="choose-theme list-unstyled mb-0">
                <li
                  data-theme="white"
                  ng-reflect-ng-class="[object Object]"
                  class="active"
                >
                  <div class="white"></div>
                </li>
                <li
                  data-theme="black"
                  ng-reflect-ng-class="[object Object]"
                  class=""
                >
                  <div class="black"></div>
                </li>
                <li
                  data-theme="purple"
                  ng-reflect-ng-class="[object Object]"
                  class=""
                >
                  <div class="purple"></div>
                </li>
                <li
                  data-theme="orange"
                  ng-reflect-ng-class="[object Object]"
                  class=""
                >
                  <div class="orange"></div>
                </li>
                <li data-theme="cyan" ng-reflect-ng-class="[object Object]">
                  <div class="cyan"></div>
                </li>
                <li
                  data-theme="green"
                  ng-reflect-ng-class="[object Object]"
                  class=""
                >
                  <div class="green"></div>
                </li>
                <li data-theme="blue" ng-reflect-ng-class="[object Object]">
                  <div class="blue"></div>
                </li>
              </ul>
            </div>
          </div>
          <div class="rightSetting">
            <h6 class="font-medium m-b-10">
              RTL Layout
            </h6>
            <mat-slide-toggle
              class="mat-slide-toggle mt-2 mat-accent"
              ng-reflect-checked="false"
              id="mat-slide-toggle-1"
              ><label
                class="mat-slide-toggle-label"
                for="mat-slide-toggle-1-input"
                ><span
                  class="mat-slide-toggle-bar mat-slide-toggle-bar-no-side-margin"
                  ><input
                    type="checkbox"
                    role="switch"
                    class="mat-slide-toggle-input cdk-visually-hidden"
                    id="mat-slide-toggle-1-input"
                    tabindex="0"
                    aria-checked="false"/><span
                    class="mat-slide-toggle-thumb-container"
                    ><span class="mat-slide-toggle-thumb"></span
                    ><span
                      mat-ripple=""
                      class="mat-ripple mat-slide-toggle-ripple mat-focus-indicator"
                      ng-reflect-trigger="[object HTMLLabelElement]"
                      ng-reflect-disabled="false"
                      ng-reflect-centered="true"
                      ng-reflect-radius="20"
                      ng-reflect-animation="[object Object]"
                      ><span
                        class="mat-ripple-element mat-slide-toggle-persistent-ripple"
                      ></span></span></span></span
                ><span class="mat-slide-toggle-content"
                  ><span style="display: none;">&nbsp;</span></span
                ></label
              ></mat-slide-toggle
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {};
</script>

<style scoped src="@/assets/dashtem.css"></style>
