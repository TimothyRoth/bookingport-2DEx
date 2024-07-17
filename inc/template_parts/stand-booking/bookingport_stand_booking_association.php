<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table);
$current_user = wp_get_current_user();
$user_role = $current_user->roles[0]; ?>

<div class="form">

    <p>
        <label>Name / Bezeichnung*</label>
        <input name="association_name" type="text" required>
    </p>

    <?php if ($user_role !== 'schausteller'): ?>
        <p>
            <label>Sortiment</label>
            <input name="association_sortiment" type="text">
        </p>
    <?php endif; ?>

    <?php if ($user_role === 'schausteller'): ?>
        <p>
            <label>Geschäft*</label>
            <input name="association_ride" type="text" required>
        </p>
    <?php endif; ?>


    <div class="stand-selection">
        <h3>Standauswahl</h3>
        <div class="stand-size-selection-wrapper" id="stand-size-selection-wrapper-verein">

            <div class="radio-selection">
                <p>
                    <label>Ich benötige einen Stromanschluss*</label>
                </p>
                <div>
                    <p>
                        <label for="electricity-yes">Ja</label>
                        <input type="radio" id="electricity-yes" name="electricity-required" value="Ja"
                               required>
                    </p>
                    <p>
                        <label for="electricity-no">Nein</label>
                        <input type="radio" id="electricity-no" name="electricity-required" value="Nein" checked required>
                    </p>
                </div>
            </div>

            <div class="radio-selection">
                <p>
                    <label>Ich benötige einen Wasseranschluss*</label>
                </p>
                <div>
                    <p>
                        <label for="water-yes">Ja</label>
                        <input type="radio" id="water-yes" name="water-required" value="Ja" required>
                    </p>
                    <p>
                        <label for="water-no">Nein</label>
                        <input type="radio" id="water-no" name="water-required" value="Nein" checked required>
                    </p>
                </div>
            </div>

            <?php if ($user_role === 'gewerblich' || $user_role === 'verein'): ?>
                <div class="radio-selection">
                    <p>
                        <label>Imbiss*</label>
                    </p>
                    <div>
                        <p>
                            <label for="eating-yes">Ja</label>
                            <input type="radio" id="eating-yes" name="sales-food" value="Ja"
                                   required>
                        </p>
                        <p>
                            <label for="eating-no">Nein</label>
                            <input type="radio" id="eating-no" name="sales-food" value="Nein" checked required>
                        </p>
                    </div>
                </div>
                <div class="radio-selection">
                    <p>
                        <label>Getränke*</label>
                    </p>
                    <div>
                        <p>
                            <label for="drinking-yes">Ja</label>
                            <input type="radio" id="drinking-yes" name="sales-drinks" value="Ja"
                                   required>
                        </p>
                        <p>
                            <label for="drinking-no">Nein</label>
                            <input type="radio" id="drinking-no" name="sales-drinks" value="Nein" checked required>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="radio-selection">
                <p>
                    <label>Benötigte Standfläche*</label>
                </p>
                <p>
                    <input type="radio" id="equal" name="space-required" value="equal" checked required>
                    <label for="equal">Eine Standeinheit mit 3 m</label>
                </p>
                <p>
                    <input type="radio" id="more" name="space-required" value="more" required>
                    <label for="more">Ich brauche mehr als 3 m</label>
                </p>
            </div>

            <div class="stand-size" id="stand-size-verein">
                <p>
                    <label>Gewünschte Standfläche*</label>
                </p>
                <p>
                    <input type="text" name="stand_width" placeholder="Breite in m" required>
                </p>
                <p>
                    <input type="text" name="stand_depth" placeholder="Tiefe in m" required>
                </p>
            </div>
        </div>

        <p class="stand-prefered-address">
            <label>Wunschstandort angeben*<br/>(Bitte direkt aus dem Dropdown wählen)</label>
            <input type="text" name="prefered_address" placeholder="Straße + Standnummer" autocomplete="off" required>
        </p>
        <div class="prefered-street-results">
            <ul></ul>
        </div>

        <div class="remarks-container">
            <h3>Sonstiges</h3>
            <p>Anmerkungen / Wünsche</p>
            <textarea name="user_remarks" id="user-remarks"
                      placeholder="Haben Sie besondere Anmerkungen zu Ihrem Aufbau, Wünsche oder ähnliches? Dann hinterlassen Sie uns hier eine kurze Nachricht."></textarea>
        </div>

        <div class="checkbox-container" id="agree_on_terms">
            <input type="checkbox" id="user_terms" name="user_terms" required>
            <label for="user_terms">Mir ist bewusst, dass dies keine verbindliche Bestellung ist, sondern lediglich eine
                Anfrage die von
                einem unserer Mitartbeiter geprüft werden muss</label>
        </div>

        <div class="button-row">
            <input type="submit" class="btn-primary disabled <?= wp_get_current_user()->roles[0] ?>" id="user-checkout"
                   value="Standbuchung anfragen"/>
            <a href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>" class="btn-secondary">Abbrechen</a>
        </div>
    </div>
</div>