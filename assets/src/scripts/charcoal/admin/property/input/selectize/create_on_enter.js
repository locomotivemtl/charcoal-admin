/* global Selectize */
/**
 * Plugin: "create_on_enter" for selectize.js
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Jordi Hereu Mayo <jhereumayo@gmail.com>
 */
Selectize.define('create_on_enter', function () {
    if (this.settings.mode !== 'multi') {
        return;
    }
    var self = this;
    this.onKeyUp = (function () {
        var original = self.onKeyUp;
        return function (e) {
            if (e.keyCode === 13 && this.$control_input.val().trim() !== '') {
                self.createItem(this.$control_input.val());
            }
            return original.apply(this, arguments);
        };
    })();
});
