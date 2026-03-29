(() => {
  const root = document.querySelector("[data-simulator]");
  if (!root) return;

  const state = JSON.parse(root.dataset.simulator || "{}");
  const playersById = new Map((state.players || []).map((player) => [Number(player.id), player]));
  const groups = new Map();

  (state.sections || []).forEach((section) => {
    (section.groups || []).forEach((group) => {
      groups.set(group.key, group);
    });
  });

  const rosterLimit = Number(state.rosterLimit || 53);
  const selectedIds = new Set((state.selectedIds || []).map(Number).filter((id) => playersById.has(id)));
  const groupKeys = Array.from(groups.keys());
  let activeGroup = groupKeys[0] || null;

  const availableList = root.querySelector("[data-available-list]");
  const selectedList = root.querySelector("[data-selected-list]");
  const availableEmpty = root.querySelector("[data-available-empty]");
  const currentGroupLabel = root.querySelector("[data-current-group-label]");
  const selectedGroupLabel = root.querySelector("[data-selected-group-label]");
  const currentGroupCount = root.querySelector("[data-current-group-count]");
  const selectedGroupCount = root.querySelector("[data-selected-group-count]");
  const summaryTotal = root.querySelector("[data-summary-total]");
  const metricSelected = root.querySelector("[data-metric-selected]");
  const metricRemaining = root.querySelector("[data-metric-remaining]");
  const rosterStatus = root.querySelector("[data-roster-status]");
  const shareUrlInput = root.querySelector("[data-share-url]");
  const sharePageLink = root.querySelector("[data-share-page]");
  const shareCardLink = root.querySelector("[data-share-card]");
  const whatsappLink = root.querySelector("[data-whatsapp-link]");
  const nativeShareButton = root.querySelector("[data-native-share]");
  const copyLinkButton = root.querySelector("[data-copy-link]");
  const copyFeedback = root.querySelector("[data-copy-feedback]");
  const personalizedPanel = root.querySelector("[data-personalized-panel]");
  const personalizedTitle = root.querySelector("[data-personalized-title]");
  const authorInput = root.querySelector("[data-author-input]");
  const paletteButtons = root.querySelectorAll("[data-palette-scheme]");
  const tabButtons = root.querySelectorAll("[data-group-tab]");
  const userLocale = (navigator.language || state.locale || "en").toLowerCase();
  const usesImperial = userLocale.includes("-us") || userLocale === "en-us";
  state.personalization = state.personalization || { author: "", scheme: "navy", palettes: {} };

  function selectedIdsArray() {
    return Array.from(selectedIds).sort((a, b) => a - b);
  }

  function imageSrc(path) {
    const value = String(path || "");
    if (/^https?:\/\//i.test(value)) return value;
    return `${state.basePath}/${value.replace(/^\/+/, "")}`;
  }

  function buildShareUrl() {
    const url = new URL(`${state.basePath}/share`, window.location.origin);
    url.searchParams.set("lang", state.locale);
    url.searchParams.set("roster", selectedIdsArray().join(","));
    return url;
  }

  function playersForGroup(groupKey) {
    const group = groups.get(groupKey);
    return group ? group.players : [];
  }

  function selectedForGroup(groupKey) {
    return playersForGroup(groupKey).filter((player) => selectedIds.has(Number(player.id)));
  }

  function availableForGroup(groupKey) {
    return playersForGroup(groupKey).filter((player) => !selectedIds.has(Number(player.id)));
  }

  function updateUrlState() {
    const url = new URL(window.location.href);
    const roster = selectedIdsArray().join(",");

    if (roster) url.searchParams.set("roster", roster);
    else url.searchParams.delete("roster");

    if (state.personalization.author) url.searchParams.set("author", state.personalization.author);
    else url.searchParams.delete("author");

    if (state.personalization.scheme) url.searchParams.set("scheme", state.personalization.scheme);
    else url.searchParams.delete("scheme");

    window.history.replaceState({}, "", url.toString());
  }

  function formatPersonalizedTitle() {
    const author = String(state.personalization.author || "").trim();
    if (!author) return state.labels.selectedRoster || "Your 53-man roster";

    const template = state.labels.selectedRosterNamed || "%s's 53-man roster";
    return template.replace("%s", author);
  }

  function applyPalette() {
    const palette = state.personalization.palettes?.[state.personalization.scheme];
    if (!personalizedPanel || !palette?.colors) return;

    const colors = palette.colors;
    personalizedPanel.style.setProperty("--preview-primary", colors.primary || "#0b2545");
    personalizedPanel.style.setProperty("--preview-secondary", colors.secondary || "#7ac143");
    personalizedPanel.style.setProperty("--preview-surface", colors.surface || "#ffffff");
    personalizedPanel.style.setProperty("--preview-surface-alt", colors.surface_alt || "#d7e4f0");
    personalizedPanel.style.setProperty("--preview-text", colors.text || "#f7fbff");
    personalizedPanel.style.setProperty("--preview-ink", colors.ink || "#142033");
    personalizedPanel.style.setProperty("--preview-muted", colors.muted || "#60708a");
    personalizedPanel.style.setProperty("--preview-line", colors.line || "#b5c5d6");

    paletteButtons.forEach((button) => {
      button.classList.toggle("active", button.dataset.paletteScheme === state.personalization.scheme);
    });
  }

  function updatePersonalization() {
    if (personalizedTitle) personalizedTitle.textContent = formatPersonalizedTitle();
    applyPalette();
  }

  function renderPlayer(player, selected, buttonLabel) {
    const measurementMeta = [];
    const experience = formatExperience(player.experience);

    if (player.height_cm) {
      measurementMeta.push(formatHeight(Number(player.height_cm)));
    }

    if (player.weight_kg) {
      measurementMeta.push(formatWeight(Number(player.weight_kg)));
    }

    const avatar = player.image
      ? `<img class="player-photo" src="${encodeURI(imageSrc(player.image))}" alt="${player.name.replace(/"/g, "&quot;")}">`
      : (player.position || "?").slice(0, 3);
    const element = document.createElement("button");
    element.type = "button";
    element.className = `player-card${selected ? " selected" : ""}`;
    element.innerHTML = `
      <div class="player-avatar">${avatar}</div>
      <div>
        <div class="player-name">${player.name}</div>
        <div class="player-meta">${player.group_label || player.position}${experience ? ` · ${experience}` : ""}</div>
        <div class="hint">${measurementMeta.join(" · ")}</div>
      </div>
      <div class="player-toggle">${buttonLabel}</div>
    `;
    element.addEventListener("click", () => togglePlayer(Number(player.id)));
    return element;
  }

  function formatHeight(heightCm) {
    if (!Number.isFinite(heightCm) || heightCm <= 0) return "";
    if (!usesImperial) return `${heightCm} cm`;

    const totalInches = Math.round(heightCm / 2.54);
    const feet = Math.floor(totalInches / 12);
    const inches = totalInches % 12;
    return `${feet}'${inches}"`;
  }

  function formatWeight(weightKg) {
    if (!Number.isFinite(weightKg) || weightKg <= 0) return "";
    if (!usesImperial) return `${weightKg} kg`;

    return `${Math.round(weightKg * 2.20462262)} lbs`;
  }

  function formatExperience(value) {
    const normalized = String(value ?? "").trim();
    if (!normalized) return "";
    if (!/^\d+$/.test(normalized)) return normalized;

    const years = Number(normalized);
    if (years === 0) return state.labels.experienceRookie || "Rookie";
    if (years === 1) return `1 ${state.labels.experienceYearSingular || "year"}`;
    return `${years} ${state.labels.experienceYearPlural || "years"}`;
  }

  function updateCounts() {
    const selectedCount = selectedIds.size;
    const remaining = Math.max(rosterLimit - selectedCount, 0);

    summaryTotal.textContent = `${selectedCount}/${rosterLimit}`;
    metricSelected.textContent = String(selectedCount);
    metricRemaining.textContent = String(remaining);
    rosterStatus.textContent = selectedCount === rosterLimit ? state.labels.complete : state.labels.incomplete;

    document.querySelectorAll("[data-sidebar-count]").forEach((node) => {
      const key = node.getAttribute("data-sidebar-count");
      node.textContent = String(selectedForGroup(key).length);
    });

  }

  function updateShareLinks() {
    const shareUrl = buildShareUrl();
    const cardUrl = new URL(`${state.basePath}/share/card.svg`, window.location.origin);
    cardUrl.searchParams.set("lang", state.locale);
    cardUrl.searchParams.set("roster", selectedIdsArray().join(","));
    if (state.personalization.author) {
      shareUrl.searchParams.set("author", state.personalization.author);
      cardUrl.searchParams.set("author", state.personalization.author);
    }
    if (state.personalization.scheme) {
      shareUrl.searchParams.set("scheme", state.personalization.scheme);
      cardUrl.searchParams.set("scheme", state.personalization.scheme);
    }

    const whatsappUrl = new URL("https://wa.me/");
    whatsappUrl.searchParams.set("text", `${state.labels.shareCaption}: ${shareUrl.toString()}`);

    shareUrlInput.value = shareUrl.toString();
    sharePageLink.href = shareUrl.toString();
    shareCardLink.href = cardUrl.toString();
    whatsappLink.href = whatsappUrl.toString();
  }

  function renderGroup() {
    const group = groups.get(activeGroup);
    if (!group) return;

    const available = availableForGroup(activeGroup);
    const selected = selectedForGroup(activeGroup);

    currentGroupLabel.textContent = group.label;
    selectedGroupLabel.textContent = group.label;
    currentGroupCount.textContent = `${available.length} ${state.labels.summaryShort} ${group.count_total}`;
    selectedGroupCount.textContent = `${selected.length} ${state.labels.selected}`;

    availableList.innerHTML = "";
    selectedList.innerHTML = "";

    available.forEach((player) => availableList.appendChild(renderPlayer(player, false, "+")));
    selected.forEach((player) => selectedList.appendChild(renderPlayer(player, true, "−")));

    availableEmpty.hidden = available.length !== 0;

    tabButtons.forEach((button) => {
      button.classList.toggle("active", button.dataset.groupTab === activeGroup);
    });
  }

  function togglePlayer(playerId) {
    if (!playersById.has(playerId)) return;

    if (selectedIds.has(playerId)) selectedIds.delete(playerId);
    else if (selectedIds.size < rosterLimit) selectedIds.add(playerId);

    updateUrlState();
    updateCounts();
    updateShareLinks();
    renderGroup();
  }

  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      activeGroup = button.dataset.groupTab;
      renderGroup();
    });
  });

  authorInput?.addEventListener("input", () => {
    state.personalization.author = authorInput.value.trim().slice(0, 40);
    updatePersonalization();
    updateUrlState();
    updateShareLinks();
  });

  paletteButtons.forEach((button) => {
    button.addEventListener("click", () => {
      state.personalization.scheme = button.dataset.paletteScheme || "navy";
      updatePersonalization();
      updateUrlState();
      updateShareLinks();
    });
  });

  copyLinkButton?.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(shareUrlInput.value);
      if (copyFeedback) copyFeedback.textContent = state.labels.copyDone;
    } catch (error) {
      shareUrlInput.select();
      document.execCommand("copy");
      if (copyFeedback) copyFeedback.textContent = state.labels.copyDone;
    }
  });

  nativeShareButton?.addEventListener("click", async () => {
    if (!navigator.share) return;

    try {
      await navigator.share({
        title: document.title,
        text: state.labels.shareCaption,
        url: shareUrlInput.value,
      });
    } catch (error) {
      // Ignore cancelled share.
    }
  });

  updateCounts();
  updatePersonalization();
  updateShareLinks();
  renderGroup();
})();
